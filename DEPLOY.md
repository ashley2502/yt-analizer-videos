# Deploy gratis (Render + Neon Postgres)

La app corre en **Render** (Web Service free). La base de datos es **Neon** (Postgres free forever).

## 1. Neon (Postgres)

1. Entra a [https://console.neon.tech](https://console.neon.tech) con GitHub.
2. **New Project** → nombre `yt-analizer`.
3. Copia la connection string (**Connection string** / URI).  
   Debe verse así:
   `postgresql://USER:PASSWORD@ep-xxxx.region.aws.neon.tech/neondb?sslmode=require`
4. Guárdala: la pegarás en Render como `DB_URL`.

## 2. APP_KEY

En tu Mac, en la carpeta del proyecto:

```bash
php artisan key:generate --show
```

Copia el valor `base64:...` (lo usarás en Render).

## 3. Render (Web Service)

1. Entra a [https://dashboard.render.com](https://dashboard.render.com) con GitHub.
2. **New +** → **Web Service**.
3. Conecta el repo `ashley2502/yt-analizer-videos`.
4. Configura:
   - **Runtime:** Docker
   - **Branch:** `main`
   - **Instance type:** Free
   - **Health check path:** `/up`
5. **Environment** (variables):

| Key | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | el `base64:...` del paso 2 |
| `APP_URL` | `https://TU-SERVICIO.onrender.com` (lo ajustas cuando Render te dé la URL) |
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

6. **Create Web Service** y espera el build (puede tardar varios minutos la primera vez).

## 4. Después del primer deploy

1. Copia la URL pública (`https://....onrender.com`).
2. En Render → Environment → pon `APP_URL` con esa URL exacta.
3. Guarda → hace redeploy automático.
4. Abre la URL → login:

- Email: `hollowgrim@local.test`
- Password: `password`

## Notas

- El plan **Free** de Render se duerme tras ~15 min sin visitas; la primera carga después puede tardar ~30–60s.
- Los uploads de thumbnails viven en disco efímero (se pueden perder al redeploy). Para producción seria: S3/R2.
- Localmente puedes seguir con **SQLite** (`DB_CONNECTION=sqlite`). Postgres es para la nube.
