from io import BytesIO

from PIL import Image
from pypdf import PageObject, PdfReader, PdfWriter
from reportlab.lib.utils import ImageReader
from reportlab.pdfgen import canvas

from app.config import MAX_PAGES, OPACITY, SCALE


class WatermarkError(Exception):
    """Fallo atribuible al archivo que envio el usuario."""

    def __init__(self, code: str, message: str, status: int = 400) -> None:
        super().__init__(message)
        self.code = code
        self.message = message
        self.status = status


def _bake_opacity(image_bytes: bytes) -> ImageReader:
    try:
        Image.open(BytesIO(image_bytes)).verify()
        img = Image.open(BytesIO(image_bytes)).convert("RGBA")
    except Exception as exc:
        raise WatermarkError("invalid_image", "La imagen no es un PNG o JPG valido.") from exc

    alpha = img.getchannel("A").point(lambda v: int(v * OPACITY))
    img.putalpha(alpha)
    return ImageReader(img)


def _overlay(width: float, height: float, image: ImageReader) -> PageObject:
    buf = BytesIO()
    c = canvas.Canvas(buf, pagesize=(width, height))
    img_w, img_h = image.getSize()
    draw_w = width * SCALE
    draw_h = draw_w * img_h / img_w
    c.drawImage(image, (width - draw_w) / 2, (height - draw_h) / 2,
                draw_w, draw_h, mask="auto")
    c.save()
    buf.seek(0)
    return PdfReader(buf).pages[0]


def apply(pdf_bytes: bytes, image_bytes: bytes) -> bytes:
    if not pdf_bytes.startswith(b"%PDF-"):
        raise WatermarkError("invalid_pdf", "El archivo no es un PDF valido.")

    try:
        reader = PdfReader(BytesIO(pdf_bytes))
    except Exception as exc:
        raise WatermarkError("corrupt_pdf", "El PDF esta danado y no se pudo leer.") from exc

    if reader.is_encrypted and not reader.decrypt(""):
        raise WatermarkError("encrypted_pdf", "El PDF esta protegido con contrasena.", 422)

    if len(reader.pages) > MAX_PAGES:
        raise WatermarkError("too_many_pages", f"El PDF supera las {MAX_PAGES} paginas.", 413)

    image = _bake_opacity(image_bytes)
    writer = PdfWriter()
    overlays: dict[tuple[int, int], PageObject] = {}

    for page in reader.pages:
        w, h = float(page.mediabox.width), float(page.mediabox.height)
        key = (round(w), round(h))
        if key not in overlays:
            overlays[key] = _overlay(w, h, image)
        page.merge_page(overlays[key])
        writer.add_page(page)

    out = BytesIO()
    writer.write(out)
    return out.getvalue()
