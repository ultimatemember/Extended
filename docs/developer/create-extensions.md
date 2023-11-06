

# Create Extensions

We've created a plugin `um-wpcli` that integrates [WP-CLI](http://wp-cli.org/) for our custom commands to test & scaffold new Ultimate Member extensions. This tool will help you create extensions from scratch following our plugin file & directory structures and follow best practices with PHP(`PSR4`) & WordPress Coding Standards.

Installation

Prerequisites
- [Node.js](https://nodejs.org/) version 16 or higher.
- [WP-CLI](https://wp-cli.org/) version 2.8.1 or higher.
- [Composer](https://getcomposer.org/) version 2.5.8 or higher.
- Terminal for accessing WP-CLI via its command line interface (CLI).
    - [VSCode](https://code.visualstudio.com/) is recommended.

UM Extended plugin can be cloned with:
<CodeGroup>
  <CodeGroupItem title="Git-Clone">

```bash:no-line-numbers
gh repo clone ultimatemember/Extended
```

  </CodeGroupItem>

  <CodeGroupItem title="Curl"  active>

```bash:no-line-numbers
curl -d '' https://github.com/ultimatemember/Extended.git
```

  </CodeGroupItem>

  <CodeGroupItem title="WP-CLI" active>

```bash:no-line-numbers
wp plugin install https://github.com/ultimatemember/Extended/archive/refs/heads/main.zip --force
```

  </CodeGroupItem>
</CodeGroup>

=======
# Create Extensions

We've created a plugin `um-wpcli` that integrates [WP-CLI](http://wp-cli.org) for our custom commands to test & scaffold new Ultimate Member extensions. This tool will help you create extensions from scratch following our plugin file & directory structures and follow best practices with PHP(`PSR4`) & WordPress Coding Standards.

## Installation

#### Prerequisites
- [Node.js](https://nodejs.org/) version 16 or higher.
- [WP-CLI](https://wp-cli.org) version 2.8.1 or higher.
- [Composer](https://getcomposer.org) version 2.5.8 or higher.
- Terminal for accessing WP-CLI via its command line interface (CLI).
   - [VSCode](https://code.visualstudio.com/) is recommended.

UM Extended plugin can be cloned with:
::: code-group

```sh [Git clone]
gh repo clone ultimatemember/Extended
```

```sh [Curl]
curl -d '' https://github.com/ultimatemember/Extended.git
```

```sh [WP-CLI]
wp plugin install https://github.com/ultimatemember/Extended/archive/refs/heads/main.zip --force
```
:::

::: tip Clone in the Plugins Directory
We recommend that you clone the plugin into `/wp-content/plugins/` directory. This is how we develop our plugins. This allows us to test plugins directly on our local WordPress site and commits our changes to the repository.
:::

### Install Dependencies
Once the plugin has been cloned & extracted, run the following command within the extended directory `/wp-content/plugins/Extended/`:

```bash:no-line-numbers
composer install
```

### Activate the Plugin
Activate the Extended plugin via the Plugins manager or via WP-CLI with:

```bash:no-line-numbers
wp plugin activate Extended
```
and run the Scaffold commands below.

### Scaffold

UM Extended plugin has a `um-wpcli` extension that integrates WP-CLI to help you in creating and testing extensions. You can create a new extension with:

```bash:no-line-numbers
wp um dev scaffold robert
```

the generated file structure should look like this:

#### Install Dependencies

Once the plugin has been cloned & extracted, run the following command within the extended directory `/wp-content/plugins/Extended/`:
```sh
composer install
```

#### Activate the Plugin
Activate the Extended plugin via the Plugins manager or via WP-CLI with:
```sh
wp plugin activate Extended
```

and run the Scaffold commands below.


## Scaffold

UM Extended plugin has a `um-wpcli` extension that integrates WP-CLI to help you in creating and testing extensions. You can create a new extension with:
```sh
wp um dev scaffold robert
```
the generated file structure should look like this:

```
Extended/src/
├─ um-robert
│  ├─ src
│  │  └─ Core.php
│  │  └─ Enqueue.php
│  ├─ assets
│  │  └─ frontend/js/
│  │  └─ frontend/css/
│  │  └─ frontend/images/
│  │  └─ admin/js/
│  │  └─ admin/css/
│  │  └─ admin/images/
│  └─ um-robert.php
│  └─ composer.json
```

Once the extension has been generated, you must run `composer update` in the `Extended` directory to autoload and register the namespace & source directory of the new extension.

