# api/: the Orders API (backend)

The backend (or full-stack) members put the Laravel project here, so that `api/composer.json` exists.
The CI job "API (Laravel)" starts by itself as soon as that file is merged into main.

Usually you start from one member's Orders & Inventory API or Secure Staff API (Backend Chapters 8-9),
or a full-stack member's Nkwen Online Orders app (Chapter 9). Copy its files
into this folder (not its `.git` folder, not `vendor/`, not `.env`), keep the three Docker files that
are already here (`Dockerfile`, `docker/start.sh`, `.dockerignore`), and open a pull request.
Then delete this file.

Using another stack (Express, FastAPI, Spring Boot)? Change the "API (Laravel)" job in
`.github/workflows/ci.yml` to install and test your stack instead, and write your own Dockerfile.
