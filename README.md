# i18next-laravel-array-deployer

Made for **Laravel 5.x**, tested on Laravel 5.7. To use with [laravel-localization-to-vue](https://github.com/kg-bot/laravel-localization-to-vue), [i18next](https://github.com/i18next/i18next) and [vue-i18next](https://github.com/panter/vue-i18next). I recommend using [i18next-xhr-backend](https://github.com/i18next/i18next-xhr-backend) and [i18next-localStorage-cache](https://github.com/i18next/i18next-localStorage-cache).


Example of code in routes/web.php:
```
...
use TommasoMatteini\i18NextLaravelArrayDeployer\ArrayDeployer;
...

/* i18n next passing values to i18next-xhr-backend */

Route::get('/locales/{lng}/{ns}.json', function ($lng, $ns) {
    $locales = ExportLocalization::export()->toArray(); // <-- laravel-localization-to-vue
    $results = isset($locales[$lng][$ns]) ? $locales[$lng][$ns] : [];
    $deployer = new ArrayDeployer($results); // <-- i18next-laravel-array-deployer
    return response()->json($deployer->getData());
});

Route::post('/locales/{lng}/{ns}', function ($lng, $ns) {
    // do something with missing namespaces ($ns) and languages ($lng)
});
```


Example of code in app/Providers/AppServiceProvider.php:
```
...
use Illuminate\Support\Facades\View;
use KgBot\LaravelLocalization\Facades\ExportLocalizations as ExportLocalization;
use TommasoMatteini\i18NextLaravelArrayDeployer\ArrayDeployer;
...


class AppServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $translation = array();
        foreach (ExportLocalization::export()->toArray()['json'] as $key => $lng) { <-- laravel-localization-to-vue (hese used for making available json-based translations)
            $translation[$key]['translation'] = $lng;
        }
        $deployer = new ArrayDeployer($translation); <-- i18next-laravel-array-deployer
        View::share('translation', $deployer->getData());
        //
    }
    
    ...
```


Example of code in app.js:
```
import i18next from 'i18next';
import Backend from 'i18next-xhr-backend';
import VueI18Next from '@panter/vue-i18next';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./params');
require('./bootstrap');

window.Vue = require('vue');


/**
 * Language bundle
 */

Vue.use(VueI18Next);

let i18nextLib = i18next.use(Backend);

i18nextLib.init({
        ns: ['auth'], // here we must define namespaces
        lng: window.Laravel.locales.lng,  // window.Laravel.locales.lng => you should implement your solution
        fallbackLng: window.Laravel.locales.fallbackLng,  // window.Laravel.locales.fallbackLng => ^ as above ^
        debug: true,
        backend: {
            customHeaders: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken  // window.Laravel.csrfToken => ^ as above ^
            },
            loadPath: window.Laravel.url('/locales/{{lng}}/{{ns}}.json'),
            addPath: window.Laravel.url('/locales/{{lng}}/{{ns}}'),
            parse: function (data) {
                console.log(data);
                return JSON.parse(data);
            },
        },
    }, function () {
        var userLang = navigator.language || navigator.userLanguage;
        console.log(i18next); // i18next object for debugging
        console.log ("The language is: " + userLang); // this will output current language
        console.log("Translated value = " + i18next.t("auth:failed")); // this will output failed message with auth namespace 
        console.log("Translated value = " + i18next.t("auth:throttle", { seconds: '1207' })); // this will output throttle message with auth namespace, {{seconds}} interpolation will be replaced
        console.log("Translated value = " + i18next.t("Hello World")); // this will be translated 
    }
);


Object.keys(window.Laravel.locales.translations).forEach(function(k){
    i18nextLib.addResourceBundle(k, 'translation', window.Laravel.locales.translations[k].translation, true, true); // this will add non-xhr languages loaded in view
});

let i18n = new VueI18Next(i18next);

...

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

var data = {
    earnings: {},
    sales: {}
};

var app = new Vue({
    el: '#app',
    data: data,
    i18n: i18n,
    watch: {

    }
});

console.log(i18next.t('I love programming.')); // this is won't function (you will need something like -> https://github.com/i18next/jquery-i18next, to make this)
console.log(i18next.t('Hello World')); // ^ as above ^

```


My solution to get laravel global variables, in my resources/views/layouts/app.blade.php
```
...
<head>
...

<!-- Laravel global variables -->
<script type="text/javascript" defer>
    window.Laravel = {
        'csrfToken': '{{ csrf_token() }}',
        'baseUrl': '{{ url('/') }}',
        'locales': {
            'lng': '{{ app()->getLocale() }}',
            'fallbackLng': '{{ config('app.fallback_locale') }}',
            'translations': @if ($translation) @json($translation) @else {} @endif
        }
    };
</script>

...
```

Installation
------------

Use [Composer] to install the package:

```
$ composer require tommasomatteini/i18next-laravel-array-deployer
```