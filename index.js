import 'babel-polyfill';
import './src/css/app.styl';
require('viewport-units-buggyfill').init();
import App from './src/js';

document.addEventListener("DOMContentLoaded", () => {
  App.init();
});
