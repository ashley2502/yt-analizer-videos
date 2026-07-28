# YT Analyzer Videos

Laravel + Inertia (React) para crecer [@HollowGrimBeats](https://www.youtube.com/@HollowGrimBeats).

## Qué hace

| Módulo | Uso |
|---|---|
| **Schedule** (móvil/desktop) | Tareas semanales, rachas, meta 2 beats + 4 Shorts |
| **Beats** | Pipeline idea → sketch → mix → ready → uploaded |
| **Auditor thumbnail** | Heurística local (letterbox, resolución) + visión IA |
| **Pack YouTube** | Títulos, descripción, tags, capítulos, checklist |
| **Shorts** | Ventanas de corte sin cámara + workflow CapCut |
| **Brief** | BPM, key, estructura y plan de sesión 25 min |

## IA

Sí: la app usa **OpenAI** (configurable) para:

- Ver y puntuar thumbnails
- Generar títulos / descripciones / tags
- Plan de Shorts
- Briefs de producción

Sin `OPENAI_API_KEY` funciona igual con **templates + heurística local** (útil para arrancar).

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-5-nano
OPENAI_VISION_MODEL=gpt-5-nano
```

## Setup local

```bash
cd ~/Documents/yt-analizer-videos
composer install
npm install
cp .env.example .env   # si hace falta
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev
# otra terminal:
php artisan serve
```

Abre `http://localhost:8000` (o tu dominio Valet `.test`).

**Login demo**

- Email: `hollowgrim@local.test`
- Password: `password`

Local usa **SQLite**. Producción usa **Postgres (Neon)**.

## Deploy en la nube (gratis)

Ver guía paso a paso: [`DEPLOY.md`](./DEPLOY.md)

Resumen: **Neon** (Postgres) + **Render** (Docker Web Service free).

## Estructura útil

- `app/Services/Analysis/*` — motores de análisis
- `app/Services/Ai/OpenAiService.php` — cliente OpenAI
- `config/ai.php` — canal HollowGrim + modelos
- `resources/js/pages/schedule` — lista de tareas
- `resources/js/pages/tools/*` — analizadores desktop
