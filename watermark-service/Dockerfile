FROM python:3.13-slim AS base
WORKDIR /srv
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY app ./app

FROM base AS test
COPY requirements-dev.txt .
RUN pip install --no-cache-dir -r requirements-dev.txt
COPY pyproject.toml .
COPY tests ./tests
CMD ["python", "-m", "pytest", "-q"]

FROM base AS runtime
RUN useradd -m -u 1000 svc
USER svc
EXPOSE 8001
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]
