import pandas as pd
from fuzzywuzzy import fuzz

# 1️⃣ Cargar el archivo Excel
df = pd.read_excel("CatalogoActividades.xlsx")

# 2️⃣ Limpiar el nombre de las columnas (por si hay espacios o mayúsculas)
df.columns = df.columns.str.strip().str.upper()

# 3️⃣ Verifica el nombre real de la columna
print("Columnas detectadas:", df.columns.tolist())

# 4️⃣ Función para limpiar texto (puedes ajustar según necesites)
def limpiar_texto(texto):
    if isinstance(texto, str):
        texto = texto.lower().strip()
        texto = texto.replace(" de ", " ")
        texto = texto.replace(" el ", " ")
        texto = texto.replace(" la ", " ")
        texto = texto.replace(" los ", " ")
        texto = texto.replace(" las ", " ")
        texto = texto.replace(" y ", " ")
        return texto
    return texto

# 5️⃣ Aplicar limpieza a la columna ACTIVIDADES
df["ACTIVIDAD_LIMPIA"] = df["ACTIVIDADES"].apply(limpiar_texto)

# 6️⃣ Eliminar duplicados aproximados (fuzzy matching)
catalogo = []
for act in df["ACTIVIDAD_LIMPIA"].unique():
    if isinstance(act, str) and not any(fuzz.ratio(act, c) > 85 for c in catalogo):
        catalogo.append(act)

# 7️⃣ Mostrar el catálogo limpio
print("\nCatálogo de actividades depurado:")
for c in catalogo:
    print("-", c)

# 8️⃣ (Opcional) Guardar el catálogo limpio en un nuevo Excel
pd.DataFrame({"CATALOGO_ACTIVIDADES": catalogo}).to_excel("catalogo_actividades.xlsx", index=False)
print("\n✅ Archivo 'catalogo_actividades.xlsx' generado correctamente.")
