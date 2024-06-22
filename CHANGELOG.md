# Yii View Renderer Change Log

## 7.0.0 under development

- Chg #115: Only a full path can now be used as a layout (@vjik)
- Chg #116, #117, #118: Rename package to `yiisoft/yii-view-renderer` (@vjik)
- Chg #119: Change package configuration parameters `viewPath` and `layout` to null (@vjik)
- Chg #64: Refactor controller name extractor, use greedy search of namespace items with "controller(s)" postfix (@vjik)

## 6.1.1 June 06, 2024

- Bug #112: Fix events configuration for Yii Debug (@vjik)

## 6.1.0 May 28, 2024

- New #102: Add layout specific injections (@vjik)
- Enh #107: Implement lazy loading for injections (@vjik)
- Enh #79: Add debug collector for yiisoft/yii-debug (@xepozz)
- Enh #99: Make `viewPath` in `ViewRenderer` constructor optional (@vjik)
- Bug #82: Fix find for layout file due to compatibility with `yiisoft/view` (@rustamwin)

## 6.0.0 February 16, 2023

- Chg #72: Adapt configuration group names to Yii conventions (@vjik)
- Enh #73: Add support for `yiisoft/aliases` version `^3.0` (@vjik)
- Enh #74: Add support for `yiisoft/csrf` version `^2.0` (@vjik)
- Enh #76: Add support for `yiisoft/data-response` version `^2.0` (@vjik)
- Enh #77: Add support for `yiisoft/view` version `^8.0` (@vjik)

## 5.0.1 December 07, 2022

- Enh #70: Add support `yiisoft/view` of version `^7.0` and `yiisoft/html` of version `^3.0` (@vjik)
- Bug #61: Fixed getting incorrect controller name based on controller instance (@vjik, @kamarton)

## 5.0.0 July 23, 2022

- New #51: Add immutable method `ViewRenderer::withLocale()` that set locale (@thenotsoft)

## 4.0.3 February 04, 2022

- Chg #50: Update the `yiisoft/view` dependency, added `^5.0` (@thenotsoft)

## 4.0.2 November 22, 2021

- Chg #48: Update the `yiisoft/csrf` dependency to `^1.2` (@devanych)

## 4.0.1 October 25, 2021

- Chg #47: Update the `yiisoft/view` dependency to `^4.0` (@vjik)

## 4.0.0 October 21, 2021

- Chg #45: `CsrfInjection` now injects a stringable CSRF object with methods `getToken()`,
  `getParameterName()`, `getHeaderName()` and `hiddenInput()` instead of string token to common parameters (@vjik)

## 3.0.0 September 18, 2021

- Chg: Replace interface `ContentParametersInjectionInterface` to `CommonParametersInjectionInterface` that inject
  parameters both to content and to layout (@vjik)
- Bug #42: Fixed not passing common parameters setted in process content rendering to layout (@vjik)

## 2.0.1 September 14, 2021

- Bug #40: Fixed not passing content and layout parameters injections to nested view rendering (@vjik)

## 2.0.0 August 24, 2021

- Chg: Use yiisoft/html ^2.0 and yiisoft/view ^2.0 (@samdark)

## 1.0.0 July 05, 2021

- Initial release.
