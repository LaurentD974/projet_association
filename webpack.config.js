const Encore = require('@symfony/webpack-encore');

Encore
    // Répertoire où les fichiers compilés seront stockés
    .setOutputPath('public/build/')

    // URL publique utilisée par le serveur pour accéder aux fichiers compilés
    .setPublicPath('/build')

    // Entrée principale de ton application
    .addEntry('app', './assets/app.js')

    // Active le support de Stimulus (optionnel si tu l’utilises)
    .enableStimulusBridge('./assets/controllers.json')

    // Active Sass/SCSS si tu utilises des fichiers .scss
    .enableSassLoader()

    // Active PostCSS (utile pour Tailwind ou Autoprefixer)
    .enablePostCssLoader()

    // Active le versioning (hash des fichiers) en production
    .enableVersioning(Encore.isProduction())

    // Active le sourcemap pour le debug
    .enableSourceMaps(!Encore.isProduction())

    // Nettoie le dossier build avant chaque compilation
    .cleanupOutputBeforeBuild()

    // Active le support de Babel
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3,
    })

    // ✅ Obligatoire : active le runtime chunk
    .enableSingleRuntimeChunk();

module.exports = Encore.getWebpackConfig();