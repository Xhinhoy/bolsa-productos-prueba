from fastapi import FastAPI, File, Response, UploadFile
from fastapi.responses import JSONResponse

from app.config import MAX_IMAGE_MB, MAX_PDF_MB
from app.watermark import WatermarkError, apply

app = FastAPI(title="Watermark Service")


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


def _read(upload: UploadFile, max_mb: int, field: str) -> bytes:
    data = upload.file.read()
    if not data:
        raise WatermarkError("empty_file", f"El campo {field} llego vacio.", 422)
    if len(data) > max_mb * 1024 * 1024:
        raise WatermarkError("file_too_large", f"El campo {field} supera {max_mb} MB.", 413)
    return data


@app.post("/watermark")
def watermark(
    pdf_file: UploadFile = File(...),
    watermark_image: UploadFile = File(...),
) -> Response:
    try:
        pdf = _read(pdf_file, MAX_PDF_MB, "pdf_file")
        image = _read(watermark_image, MAX_IMAGE_MB, "watermark_image")
        return Response(apply(pdf, image), media_type="application/pdf")
    except WatermarkError as exc:
        return JSONResponse(
            {"code": exc.code, "message": exc.message},
            status_code=exc.status,
        )
