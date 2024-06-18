# Upgrading Instructions for Yii View Renderer

This file contains the upgrade notes. These notes highlight changes that could break your
application when you upgrade the package from one version to another.

> **Important!** The following upgrading instructions are cumulative. That is, if you want
> to upgrade from version A to version C and there is version B between A and C, you need
> to following the instructions for both A and B.

## Upgrade from 6.x

- Change layout value that passed to `ViewRenderer` constructor and `withLayout()` method to full path.
- Change namespace `Yiisoft\Yii\View\*` to `Yiisoft\Yii\View\Renderer\*`.
- Rename package configuration parameters key from "yiisoft/yii-view" to "yiisoft/yii-view-renderer".

- Now configuration parameters `viewPath` and `layout` is null by default. If your application requires other values add
  them to the configuration parameters on application level. For example:

```php
'yiisoft/yii-view-renderer' => [
    'viewPath' => '@views',
    'layout' => '@layout/main.php',
],
```
