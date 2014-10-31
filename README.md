# ExozetGruntBundle

This small bundle is meant to run the following steps, if the cache is cleaned and warmed up (optional):

* npm install
* bower install
* grunt dev

## Usage

If you have a `package.json`, `bower.json` and `Gruntfile.js` in your project, add the bundle to your project.

As soon a you call

``` console
$ app/console c:c
``` 

and the steps by grunt will be executed.

## Configuration

``` yaml
parameters:
    grunt.npm_binary_path: 'npm'
    grunt.bower_binary_path: 'bower'
    grunt.grunt_binary_path: 'LANG=en_US.UTF-8 grunt'
    grunt.grunt_task: 'dev'
    grunt.environments:
        - dev
```

This configuration will run the `dev` task ONLY in `dev` environment. The `LANG` environment variable is
e.g. required for compass.

## License

Copyright 2014 by Exozet and licensed under MIT License.
