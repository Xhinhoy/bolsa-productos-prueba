from io import BytesIO

from fastapi.testclient import TestClient
from PIL import Image
from pypdf import PdfReader, PdfWriter
from reportlab.pdfgen import canvas

from app.main import app

client = TestClient(app)


def make_pdf(sizes=((595, 842), (595, 842)), rotate=0) -> bytes:
    buf = BytesIO()
    c = canvas.Canvas(buf)
    for w, h in sizes:
        c.setPageSize((w, h))
        c.drawString(50, 50, "contrato")
        if rotate:
            c.setPageRotation(rotate)
        c.showPage()
    c.save()
    return buf.getvalue()


def make_png() -> bytes:
    buf = BytesIO()
    Image.new("RGBA", (200, 100), (255, 0, 0, 255)).save(buf, "PNG")
    return buf.getvalue()


def post(pdf=None, img=None):
    files = {}
    if pdf is not None:
        files["pdf_file"] = ("c.pdf", pdf, "application/pdf")
    if img is not None:
        files["watermark_image"] = ("l.png", img, "image/png")
    return client.post("/watermark", files=files)


def images_per_page(pdf_bytes):
    return [len(page.images) for page in PdfReader(BytesIO(pdf_bytes)).pages]


def test_health():
    assert client.get("/health").json() == {"status": "ok"}


def test_marca_en_todas_las_paginas():
    r = post(make_pdf(), make_png())
    assert r.status_code == 200
    counts = images_per_page(r.content)
    assert len(counts) == 2
    assert all(n >= 1 for n in counts)


def test_tamanos_de_pagina_mixtos():
    r = post(make_pdf(((595, 842), (842, 595), (300, 300))), make_png())
    assert r.status_code == 200
    assert all(n >= 1 for n in images_per_page(r.content))


def test_pagina_rotada():
    r = post(make_pdf(rotate=90), make_png())
    assert r.status_code == 200
    assert all(n >= 1 for n in images_per_page(r.content))


def test_no_es_pdf():
    r = post(make_png(), make_png())
    assert r.status_code == 400
    assert r.json()["code"] == "invalid_pdf"


def test_pdf_corrupto():
    assert post(b"%PDF-1.4 basura sin xref", make_png()).status_code == 400


def test_pdf_cifrado():
    writer = PdfWriter(clone_from=BytesIO(make_pdf()))
    writer.encrypt("secreto")
    buf = BytesIO()
    writer.write(buf)
    assert post(buf.getvalue(), make_png()).status_code == 422


def test_imagen_invalida():
    r = post(make_pdf(), b"no soy una imagen")
    assert r.status_code == 400
    assert r.json()["code"] == "invalid_image"


def test_campo_faltante():
    assert post(make_pdf()).status_code == 422
