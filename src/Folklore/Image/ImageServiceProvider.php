<?php namespace Folklore\Image;

use Illuminate\Support\ServiceProvider;

class ImageServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Config file path
        $configFile = __DIR__ . '/../../resources/config/thumbnail.php';
        $publicFile = __DIR__ . '/../../resources/assets/';

        // Merge files
        $this->mergeConfigFrom($configFile, 'thumbnail');

        // Publish
        $this->publishes([
            $configFile => config_path('thumbnail.php')
        ], 'config');
        
        $this->publishes([
            $publicFile => public_path('vendor/folklore/image')
        ], 'public');

        $app = $this->app;
        $router = $app['router'];

        
        $pattern = $app['thumbnail']->pattern();
        $proxyPattern = config('thumbnail.proxy_route_pattern');

        $router->pattern('image_pattern', $pattern);
        $router->pattern('image_proxy_pattern', $proxyPattern ? $proxyPattern:$pattern);

        //Serve image
        $serve = config('thumbnail.serve') ;

        if ($serve) {
            // Create a route that match pattern
            $serveRoute = config('thumbnail.serve_route', '{image_pattern}');

            $router->get($serveRoute, array(
                'as' => 'thumbnail.serve',
                'domain' => config('thumbnail.domain'),
                'uses' => 'Folklore\Image\ImageController@serve'
            ));
        }
        
        //Proxy
        $proxy = config('thumbnail.proxy');
        if ($proxy) {
            $serveRoute = config('thumbnail.proxy_route');
            $router->get($serveRoute, array(
                'as' => 'thumbnail.proxy',
                'domain' => config('thumbnail.proxy_domain'),
                'uses' => 'Folklore\Image\ImageController@proxy'
            ));
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('thumbnail', function ($app) {
            return new ImageManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('image');
    }
}
