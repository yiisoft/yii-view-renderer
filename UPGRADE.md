# Upgrading Instructions for Yii View Renderer

This file contains the upgrade notes. These notes highlight changes that could break your
application when you upgrade the package from one version to another.

> **Important!** The following upgrading instructions are cumulative. That is, if you want
> to upgrade from version A to version C and there is version B between A and C, you need
> to following the instructions for both A and B.

## Upgrade from 6.x

- Change layout value that passed to `ViewRenderer` constructor and `withLayout()` method to full path.
- Rename package configuration parameters key from "yiisoft/yii-view" to "yiisoft/yii-view-renderer".
