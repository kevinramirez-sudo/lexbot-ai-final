from typing import List
import unicodedata

from fastapi import FastAPI
from pydantic import BaseModel, Field

app = FastAPI(
    title="LexBot AI - Servicio de análisis jurídico",
    version="1.0.0",
    description="Microservicio FastAPI para clasificar casos jurídicos por especialidad y prioridad.",
)


class AnalisisRequest(BaseModel):
    descripcion: str = Field(min_length=5, max_length=5000)


class AnalisisResponse(BaseModel):
    especialidad: str
    prioridad: str
    motivo: str
    palabras_detectadas: List[str]


def normalizar(texto: str) -> str:
    texto = unicodedata.normalize("NFD", texto.lower().strip())
    texto = "".join(
        caracter
        for caracter in texto
        if unicodedata.category(caracter) != "Mn"
    )
    return " ".join(texto.split())


def detectar(texto: str, palabras: List[str]) -> List[str]:
    return [palabra for palabra in palabras if palabra in texto]


REGLAS = {
    "Penal": [
        "accidente", "robo", "delito", "agresion", "violencia", "amenaza",
        "estafa", "denuncia", "secuestro", "extorsion",
    ],
    "Familiar": [
        "divorcio", "hijos", "pension", "custodia", "alimentos", "familia",
        "violencia intrafamiliar", "tenencia",
    ],
    "Laboral": [
        "despido", "despidieron", "despedido", "trabajo", "laboral", "sueldo",
        "salario", "vacaciones", "contrato laboral", "acoso laboral", "no me pagan",
        "no me quiere pagar", "no me quieren pagar", "no me pagaron", "no me paga",
    ],
    "Civil": [
        "deuda", "contrato", "propiedad", "arriendo", "alquiler", "herencia",
        "terreno", "casa", "incumplimiento",
    ],
}


@app.get("/")
@app.get("/salud")
def salud():
    return {
        "servicio": "LexBot AI - Análisis jurídico",
        "estado": "activo",
        "puerto": 8003,
    }


@app.post("/analizar-caso", response_model=AnalisisResponse)
def analizar_caso(datos: AnalisisRequest):
    texto = normalizar(datos.descripcion)

    penal = detectar(texto, REGLAS["Penal"])
    if penal:
        return AnalisisResponse(
            especialidad="Penal",
            prioridad="Alta",
            motivo="Se detectaron términos relacionados con un posible delito, agresión, amenaza o denuncia.",
            palabras_detectadas=penal,
        )

    familiar = detectar(texto, REGLAS["Familiar"])
    if familiar:
        prioridad = "Alta" if any(
            termino in texto
            for termino in ["violencia intrafamiliar", "custodia", "tenencia"]
        ) else "Media"
        return AnalisisResponse(
            especialidad="Familiar",
            prioridad=prioridad,
            motivo="El caso parece relacionado con familia, divorcio, hijos, pensión o custodia.",
            palabras_detectadas=familiar,
        )

    laboral = detectar(texto, REGLAS["Laboral"])
    if laboral:
        prioridad = "Alta" if any(
            termino in texto
            for termino in [
                "despido", "despidieron", "despedido", "acoso laboral",
                "no me pagan", "no me quiere pagar", "no me quieren pagar",
                "no me pagaron", "no me paga",
            ]
        ) else "Media"
        return AnalisisResponse(
            especialidad="Laboral",
            prioridad=prioridad,
            motivo="El caso parece relacionado con empleo, despido, salario o condiciones laborales.",
            palabras_detectadas=laboral,
        )

    civil = detectar(texto, REGLAS["Civil"])
    if civil:
        return AnalisisResponse(
            especialidad="Civil",
            prioridad="Media",
            motivo="El caso parece relacionado con contratos, deudas, propiedades, arriendos o herencias.",
            palabras_detectadas=civil,
        )

    return AnalisisResponse(
        especialidad="Civil",
        prioridad="Media",
        motivo="No se detectaron palabras clave específicas. Se recomienda una revisión inicial por un abogado civil.",
        palabras_detectadas=[],
    )
