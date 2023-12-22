# Architect
A contao tool that allows you to quickly generate necessary files and configurations such as content elements or frontend modules.

## Requirements
- PHP ^8.x
- Contao ^5.x 

## Installation

```
composer require konradx3/architect-command-bundle --dev
```


## Usage

You can change and configure each generated file to suit your needs.
It's just a generator

### Create custom bundle.
```
php contao-console architect:create:bundle [bundleName] [directory] [--namespace]
```
- [controller] - optional, CustomContaoApp or CustomContaoAppBundle, default AppBundle
- [directory] - optional, path/your/custom-bundle, default App/src/...
- [--namespace] - optional, if you need custom namespace you can type here your namespace

##### Important
It will add to .env file BUNDLE_NAMESPACE variable for remember your namespace, when you will create content elements you don't have to type --namespace all time

### Generate content element.
```
php contao-console architect:make:content-element [controller] [directory] [--namespace]
```
- [controller] - required, FooBarController
- [directory] - optional, path/your/custom-bundle, default App/src/...
- [--namespace] - optional, if you need custom namespace you can type here your namespace

### Generate frontend module.
```
php contao-console architect:make:frontend-module [controller] [directory] [--namespace]
```
- [controller] - required, FooBarController
- [directory] - optional, path/your/custom-bundle, default App/src/...
- [--namespace] - optional, if you need custom namespace you can type here your namespace

### Generate controller.
```
php contao-console architect:make:controller [controller] [type] [directory] [--namespace]
```
- [controller] - required, FooBarController
- [type] - optional, FMD - frontend module or CTE - content element
- [directory] - optional, path/your/custom-bundle, default App/src/...
- [--namespace] - optional, if you need custom namespace you can type here your namespace

### Generate controller settings in services.yaml.
```
php contao-console architect:make:controller-config-services [controller] [type] [directory] [--namespace]
```
- [controller] - required, FooBarController
- [type] - required, FMD - frontend module or CTE - content element
- [directory] - optional, path/your/custom-bundle, default App/src/...
- [--namespace] - optional, if you need custom namespace you can type here your namespace

### Generate controller settings in dca.
```
php contao-console architect:make:controller-config-dca [controller] [type] [directory]
```
- [controller] - required, FooBarController
- [type] - required, FMD - frontend module or CTE - content element
- [directory] - optional, path/your/custom-bundle, default App/src/...

### Generate twig template for controller.
```
php contao-console architect:make:controller-template [controller] [type] [directory]
```
- [controller] - required, FooBarController
- [type] - required, FMD - frontend module or CTE - content element
- [directory] - optional, path/your/custom-bundle, default App/src/...
