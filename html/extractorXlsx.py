import pandas as pd
import glob
import os

# Ruta de la carpeta con los archivos
ruta_carpeta = r"C:\wamp64\www\SoftlogisV3\Remolques"

archivos = glob.glob(os.path.join(ruta_carpeta, "*.xls*"))

archivos = glob.glob(os.path.join(ruta_carpeta, "*.xls*"))

dataframes = []

for archivo in archivos:
    try:
        # Ignorar archivos temporales (~$)
        if os.path.basename(archivo).startswith("~$"):
            continue

        # Detectar extensión para usar el engine correcto
        extension = os.path.splitext(archivo)[1].lower()
        engine = "xlrd" if extension == ".xls" else "openpyxl"

        # 🔹 Leer solo columnas D y F, encabezados en la fila 2
        df = pd.read_excel(
            archivo,
            engine=engine,
            usecols="C,E",   # columnas específicas
            header=2         # fila 2 (la primera fila es 0)
        )

        # (Opcional) agrega el nombre del archivo de origen
        df["Archivo_Origen"] = os.path.basename(archivo)

        dataframes.append(df)

    except Exception as e:
        print(f"⚠️ Error leyendo {archivo}: {e}")

# 🧩 Unir todos los DataFrames
if dataframes:
    catalogo_final = pd.concat(dataframes, ignore_index=True)
    salida = os.path.join(ruta_carpeta, "catalogo_remolques_servicios_refacciones.xlsx")
    catalogo_final.to_excel(salida, index=False)
    print(f"✅ Catálogo consolidado guardado en: {salida}")
else:
    print("❌ No se encontraron datos válidos en los archivos.")