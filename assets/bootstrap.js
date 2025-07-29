import { startStimulusApp } from '@symfony/stimulus-bridge';

const app = startStimulusApp(require.context(
  './controllers',
  true,
  /\.(j|t)sx?$/
));

// app.register('your_controller_name', YourImportedController);