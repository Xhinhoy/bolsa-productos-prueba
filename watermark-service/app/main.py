from fastapi import FastAPI

app = FastAPI(title="Watermark Service")


@app.get("/health")
def health():
    return {"status": "ok"}
