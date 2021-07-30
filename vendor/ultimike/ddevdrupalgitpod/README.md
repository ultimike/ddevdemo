# DDEV + Drupal + Gitpod.io

## Description

This library adds the required files to a DDEV-Local configured Drupal 9 site so that the site can be used with Gitpod.io.

This work is 100% based on the amazing work that Ofer Shaal did in figuring out how to get a DDEV-Local powered site working on gitpod.io. See [https://github.com/shaal/ddev-gitpod](https://github.com/shaal/ddev-gitpod).

## Instructions for use

1.  Start with a Drupal 9 site (based on the drupal/recommended-project Composer template) with a DDEV-Local configuration file (.ddev/config.yaml), committed to a GitHub.com repository. 
2.  Add the following to the Drupal site's composer.json file "repositories" section (once this library is published on https://packagist.org, this step will no longer be necessary):

```
{
    "type": "vcs",
    "url": "https://github.com/ultimike/ddevdrupalgitpod"
}
```

3.  Add the following to the Drupal site's composer.json file "drupal-scaffold" section (this allows the Drupal core scaffolding module to use the ddevdrupalgitpod scaffolding files):

```
"allowed-packages": [
    "ultimike/ddevdrupalgitpod"
]
```

4.  `composer require ultimike/ddevdrupalgitpod:dev-main`

5.  Commit and push all changes to GitHub.

6.  Be sure your gitpod.io/settings have "Enable feature preview" selected.

7.  In your browser, go to https://gitpod.io/#github.com/vendor/name replacing "vendor/name" with your project vendor/name on Github.com.

8. Wait for the "A service is available on port 8080" message to appear in Gitpod.io Terminal interface.

9. Use `gp url 8080` in the Gitpod.io Terminal to view the Drupal site's URL.
