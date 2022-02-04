# Yii View Extension Change Log

## 5.0.1 under development

- no changes in this release.

## 5.0.0 February 04, 2022

- Chg #49: Update the `yiisoft/view` dependency to `^5.0` (thenotsoft)

## 4.0.2 November 22, 2021

- Chg #48: Update the `yiisoft/csrf` dependency to `^1.2` (devanych)

## 4.0.1 October 25, 2021

- Chg #47: Update the `yiisoft/view` dependency to `^4.0` (vjik)

## 4.0.0 October 21, 2021

- Chg #45: `CsrfInjection` now injects a stringable CSRF object with methods `getToken()`,
  `getParameterName()`, `getHeaderName()` and `hiddenInput()` instead of string token to common parameters (vjik)

## 3.0.0 September 18, 2021

- Chg: Replace interface `ContentParametersInjectionInterface` to `CommonParametersInjectionInterface` that inject
  parameters both to content and to layout (vjik)
- Bug #42: Fixed not passing common parameters setted in process content rendering to layout (vjik)

## 2.0.1 September 14, 2021

- Bug #40: Fixed not passing content and layout parameters injections to nested view rendering (vjik)

## 2.0.0 August 24, 2021

- Chg: Use yiisoft/html ^2.0 and yiisoft/view ^2.0 (samdark)

## 1.0.0 July 05, 2021

- Initial release.
