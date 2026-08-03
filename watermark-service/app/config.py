import os

MAX_PDF_MB = int(os.getenv("MAX_PDF_MB", 10))
MAX_IMAGE_MB = int(os.getenv("MAX_IMAGE_MB", 2))
MAX_PAGES = int(os.getenv("MAX_PAGES", 500))
OPACITY = float(os.getenv("WATERMARK_OPACITY", 0.25))
SCALE = float(os.getenv("WATERMARK_SCALE", 0.5))
