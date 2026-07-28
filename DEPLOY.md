# Deploy gratis (Render + Neon Postgres)

La app corre en **Render** (Web Service free). La base de datos es **Neon** (Postgres free forever).

URL de producción actual:

`https://yt-analizer-videos.onrender.com`

---

## 1. Neon (Postgres)

1. Entra a [https://console.neon.tech](https://console.neon.tech) con GitHub.
2. **New Project** → nombre `yt-analizer`.
3. Postgres version: **16** o **18** (ambas sirven).
4. **Neon Auth:** déjalo en **Off** (la app ya tiene login propio).
5. Copia la connection string (**Connection string** / URI).  
   Debe verse así:
   `postgresql://USER:PASSWORD@ep-xxxx.region.aws.neon.tech/neondb?sslmode=require`
6. Guárdala: la pegarás en Render como `DB_URL`.

> No subas esta URI a GitHub ni la pegues en chats públicos. Si se filtra, rota la password en Neon.

---

## 2. APP_KEY

En tu Mac, en la carpeta del proyecto:

```bash
php artisan key:generate --show
```

Copia el valor `base64:...` (lo usarás en Render).

---

## 3. Conectar el repo de GitHub a Render

Si el repo es **privado** y no aparece:

1. Render → Account Settings → **Git** / **GitHub**
2. **Configure** / **Connect**
3. En GitHub: **Only select repositories** → marca `yt-analizer-videos` → **Save**
4. Vuelve al dashboard de Render

---

## 4. Crear el Web Service

1. Entra a [https://dashboard.render.com](https://dashboard.render.com)
2. **New +** → **Web Service**
3. Elige el repo `ashley2502/yt-analizer-videos`
4. Configura:

| Campo | Valor |
|---|---|
| Name | `yt-analizer-videos` |
| Runtime | **Docker** |
| Branch | `main` |
| Instance type | **Free** |
| Health Check Path | `/up` |

5. En **Environment**, agrega estas variables:

| Key | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | el `base64:...` del paso 2 |
| `APP_URL` | `https://yt-analizer-videos.onrender.com` (ajusta si tu URL es otra) |
| `DB_CONNECTION` | `pgsql` |
| `DB_SSLMODE` | `require` |
| `DB_URL` | la URI de Neon del paso 1 |
| `LOG_CHANNEL` | `stderr` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `OPENAI_API_KEY` | (opcional) tu key |
| `OPENAI_MODEL` | `gpt-5-nano` |
| `OPENAI_VISION_MODEL` | `gpt-5-nano` |

6. **Create Web Service** y espera el build (5–15 min la primera vez).

---

## 5. Dónde cambiar variables en Render (después de creado)

1. Dashboard → haz clic en el servicio **`yt-analizer-videos`**
2. Menú izquierdo → **Environment**
   - Si no lo ves: **Settings** → sección **Environment**
3. Busca la variable (ej. `APP_URL`)
   - Si existe → **Edit**
   - Si no → **Add Environment Variable**
4. Ejemplo obligatorio tras el primer deploy:

```text
APP_URL=https://yt-analizer-videos.onrender.com
```

5. **Save Changes**
6. Render redeploya solo. Si no: **Manual Deploy** → **Deploy latest commit**

---

## 6. Login

Abre [https://yt-analizer-videos.onrender.com](https://yt-analizer-videos.onrender.com)

- Email: `hollowgrim@local.test`
- Password: `password`

---

## 7. Cómo subir cambios (deploy automático)

No hace falta “upload” manual ni GitHub Action de deploy.

```bash
git add .
git commit -m "tu mensaje"
git push origin main
```

Render detecta el push a `main` y vuelve a construir/desplegar.

El workflow de `.github/workflows/tests.yml` solo corre **tests**, no despliega.

Para ver el progreso: Render → tu servicio → **Events** / **Logs**.

---

## Notas

- El plan **Free** de Render se duerme tras ~15 min sin visitas; la primera carga después puede tardar ~30–60s.
- Los uploads de thumbnails viven en disco efímero (se pueden perder al redeploy). Para producción seria: S3/R2.
- Localmente puedes seguir con **SQLite** (`DB_CONNECTION=sqlite`). Postgres es para la nube.
