var Encore = require ('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSassLoader()

    /**
     * Styles
     */
    .addStyleEntry('css/app', ['./assets/scss/app.scss'])

    .autoProvidejQuery()

    /**
     * Scripts
     */
    .addEntry('js/app', ['./assets/js/app.js'])


;

module.exports = Encore.getWebpackConfig();