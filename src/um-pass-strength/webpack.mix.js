const mix = require("laravel-mix");
const path = require("path");
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
   resolve: {
      modules: ["node_modules"]
   }
});

mix.webpackConfig((webpack) => {
   return {
      plugins: [
         new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
            "window.jQuery": "jquery",
         })
      ],
   };
});



mix.setPublicPath(path.resolve("./")); // Fix path for standalone


mix.js("resources/js/app.js", "assets/js/um-pass-strength.js");
mix.sass("resources/sass/app.scss", "assets/css/um-pass-strength.css").version();

mix.scripts(["assets/js/um-pass-strength.js"], "assets/js/um-pass-strength.min.js")
   .options({
      processCssUrls: false
   })
   .version();

mix.styles(["assets/css/um-pass-strength.css"], "assets/css/um-pass-strength.min.css")
   .options({
      processCssUrls: false
   })
   .version();


