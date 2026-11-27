# Deploying an API (starter)

Copy into your Laravel project, keeping the folders:
- `app/Console/Commands/DeployCheck.php`: `php artisan nkwen:deploy-check`, a production checklist (finished).
  FAIL lines stop the server from starting; WARN lines are advice.
- `docker/start.sh`: two TODOs (run the checklist first; seed the demo staff).
- `.env.production.example`: write the production value or a placeholder for each setting. Never real secrets.
