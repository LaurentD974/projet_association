Set-Content -Path assets/bootstrap.js -Value @"
import { startStimulusApp } from '@symfony/stimulus-bundle';
const app = startStimulusApp(import.meta.url);
// Ex: app.register('hello', class extends Controller { ... });
export default app;
