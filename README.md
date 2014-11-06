# ExozetGruntBundle

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f33fb53d-106d-4bf9-88e4-97ab5059df49/big.png)](https://insight.sensiolabs.com/projects/f33fb53d-106d-4bf9-88e4-97ab5059df49)

[![Code Climate](https://codeclimate.com/github/exozet/ExozetGruntBundle/badges/gpa.svg)](https://codeclimate.com/github/exozet/ExozetGruntBundle) [![Test Coverage](https://codeclimate.com/github/exozet/ExozetGruntBundle/badges/coverage.svg)](https://codeclimate.com/github/exozet/ExozetGruntBundle)

This small bundle is meant to run the following steps, if the cache is cleaned and warmed up (optional):

* npm install
* bower install
* grunt dev

## Usage

Add `$bundles[] = new Exozet\GruntBundle\ExozetGruntBundle()` to your `AppKernel.php` (in the `dev`-if-block).

If you have a `package.json`, `bower.json` and `Gruntfile.js` in your project, add the bundle to your project.

As soon a you call

``` console
$ app/console c:c
``` 

and the steps by grunt will be executed.

## Configuration

``` yaml
# Default configuration for extension with alias: "exozet_grunt"
exozet_grunt:

    # The environments where the bundle should be executed
    environments:         # Required

        # Default:
        - dev

    # Use binaries with the following environment vars (key/value pairs)
    binary_env_vars:      # Example: LANG:    en_US.UTF-8 for LANG="en_US.UTF-8"

        LANG:                 en_US.UTF-8

    # The binary path where npm is located
    npm_binary_path:      npm # Example: /usr/bin/npm

    # The binary path where bower is located
    bower_binary_path:    bower # Example: /usr/bin/bower

    # The binary path where grunt is located
    grunt_binary_path:    grunt # Example: /usr/bin/grunt

    # The grunt task which should be executed
    grunt_task:           dev # Example: dev
```

This default configuration will run the `dev` task ONLY in `dev` environment.
The `LANG` environment variable is e.g. required for compass.

## License

Copyright 2014 by Exozet and licensed under MIT License.
