[![MedusaWP](https://raw.githubusercontent.com/Agilo/medusawp/master/.github/banner.png "MedusaWP")](https://github.com/Agilo/medusawp)

<h1 align="center">
  MedusaWP
</h1>

<h4 align="center">
  MedusaWP enables you to use <a href="https://wordpress.org/" target="_blank">WordPress</a> as a headless CMS or as a storefront for your <a href="https://medusajs.com/" target="_blank">Medusa</a> shop.
</h4>

<p align="center">
  <a href="./LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-blue" alt="MedusaWP is released under the MIT license." />
  </a>
  <a href="https://github.com/Agilo/medusawp?tab=readme-ov-file#contributing">
    <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat" alt="PRs welcome!" />
  </a>
  <a href="https://twitter.com/intent/follow?screen_name=Agilo">
    <img src="https://img.shields.io/twitter/follow/Agilo" alt="X (formerly Twitter) Follow">
  </a>
</p>
<p align="center">
  <a href="https://nodejs.org/" target="_blank">
    <img src="https://img.shields.io/badge/Node.js-%5E20-brightgreen" alt="Node.js ^20">
  </a>
  <a href="https://codex.wordpress.org/WordPress_Versions" target="_blank">
    <img src="https://img.shields.io/badge/WordPress-%5E5.6-blue?logo=wordpress&logoColor=white" alt="WordPress ^5.6">
  </a>
  <a href="https://www.php.net/" target="_blank">
    <img src="https://img.shields.io/badge/PHP-%5E7.4-777BB4?logo=php&logoColor=white" alt="PHP Version ^7.4">
  </a>
  <a href="https://getcomposer.org/doc/01-basic-usage.md" target="_blank">
    <img src="https://img.shields.io/badge/Composer-%5E1-orange" alt="Composer ^1">
  </a>
</p>

MedusaWP is a powerful tool that seamlessly integrates your Medusa e-commerce store with WordPress, offering a range of features to simplify your e-commerce management. We also provide a rich set of Medusa-oriented functions for you to utilize within your WordPress theme.

You can connect to your existing Medusa store and effortlessly import its data into WordPress. You have full visibility into the sync progress and can easily troubleshoot any issues that may arise during the synchronization process. Data health checks ensure that your synced items are error-free and successfully imported into WordPress. Managing media assets is a breeze with the ability to import Medusa thumbnails directly into the WordPress Media Library. You can also manage and display Medusa data as custom post types within your WordPress site.

For a seamless shopping experience, the plugin automatically creates a cart for customers who land on your site. A country switcher functionality is also included, allowing cart region updates based on the selected country code.

When needed, you can remove all synced data and disconnect from Medusa effortlessly.

Unlock the full potential of your Medusa store with the MedusaWP plugin and streamline your e-commerce operations.

## Features

- Use WordPress as a headless CMS or as a storefront for the Medusa shop.
- Connect to an existing Medusa store from WordPress and import its data into WordPress.
- Synchronize your Medusa e-commerce data with WordPress.
- Import Medusa thumbnails into the WordPress Media Library (either directly when syncing Medusa data or separately).
- Provide an overview and troubleshooting for the sync progress.
- Check the data health, including identifying any sync errors, partially synced items, or successfully synced data.
- Manage and display Medusa data (products, product collections...) in WordPress as custom post types (CPT).
- Automatically create a cart when the customer lands on the site for the first time (default country/region settings can be configured within the WordPress plugin settings).
- Implement a country switcher that updates the cart region based on the given country code.
- Remove all synced data.
- Disconnect from Medusa.
- Expose a rich set of functions available for you to use within your WordPress theme, including [`medusa-react` utility functions](https://docs.medusajs.com/medusa-react/overview#utilities) for computing and formatting prices and amounts.

## Prerequisites
- [Medusa Backend](https://docs.medusajs.com/development/backend/install)
- [Redis](https://docs.medusajs.com/development/backend/prepare-environment#redis)
- [WordPress Installation](https://developer.wordpress.org/advanced-administration/before-install/howto-install/)
  - WordPress website up and running
  - Administrator access to your WordPress website
- [PHP Composer](https://getcomposer.org/download/) latest version 1.x

## How To Install

### Medusa Plugin

1. In the root of your Medusa backend, run the following command to install the `medusa-plugin-wordpress` plugin:
```bash
npm i medusa-plugin-wordpress
```

2. Add the plugin to your `medusa-config.js` file at the end of the `plugins` array:

```js
module.exports = {
  // ...
  plugins: [
    // ...
    {
      resolve: "medusa-plugin-wordpress",
      options: {
        // ...
      },
    },
  ],
  // ...
}
```

3. Next, configure the `medusa-plugin-wordpress` plugin that you added in the previous step. For example:
```js
const plugins = [
  // ...
  {
    resolve: `medusa-plugin-wordpress`,
    options: {
      sync_options: {
        attempts: 5,
        backoff: {
          type: 'exponential',
          delay: 2000
        }
      }
    },
  },
]
```

#### Configuration Options

The plugin can be configured with the following options:
```js
(property) sync_options: {
    delay?: number | undefined;
    attempts: number;
    backoff?: {
        type: "fixed" | "exponential";
        delay: number;
    } | undefined;
}
```

- `sync_options` - Sync options object.
  - `delay` - Delay time in milliseconds, default `undefined`.
  - `attempts` - Number of attempts, default `3`.
  - `backoff` - Backoff options object.
    - `type` - Backoff type, either `"fixed"` or `"exponential"`, default `"exponential"`.
    - `delay` - Backoff delay time in milliseconds, default `2000`.

### WordPress Plugin
1. In the root directory of your WordPress installation, run the following command to install the plugin using Composer:
```bash
composer require agilo/medusawp
```

2. Log in to your WordPress admin dashboard using your administrator credentials.

3. After successful installation, click the "Activate" button to activate the plugin.

4. To finish the plugin setup, proceed to the [How To Use](#how-to-use) steps.

## How To Use
1. Run the following command in the directory of the Medusa backend to run the backend:
```bash
npm run start
```

2. After you have logged into your WordPress site, navigate to the MedusaWP plugin settings page, which can be found in the WordPress admin menu.

3. Connect your Medusa e-commerce store and your WordPress instance. On the default MedusaWP plugin screen enter your Medusa shop URL and credentials - email and password and press "Connect" button.

> [!NOTE]
> If necessary, you can always disconnect from your Medusa shop by opening a dropdown in the upper-right corner, pressing the "Disconnect" button, and confirming your decision.

4. When the connection with Medusa is established, sync your Medusa e-commerce data with WordPress by clicking the "Sync" button. You can also choose to import Medusa thumbnails into the WordPress Media Library directly with the data sync (this may slightly slow down your data sync process), or you can import thumbnails separately later using the "Import" button.

> [!NOTE]
> It is possible to remove all your synced Medusa data from your WordPress site at any time using the "Remove" button. We recommend that you backup your data before proceeding with this action.

5. Once the data synchronization is complete, configure the default settings of the MedusaWP plugin to suit your needs. In the dropdown, you can select the default country, which will be used to automatically create a cart in the corresponding region when a customer lands on the site.

6. Verify that the plugin is working as expected by visiting your website and using its features.

That's it! You have successfully installed and set up the MedusaWP plugin. Enjoy using it in your projects and on your WordPress website.

## Contributing

We welcome contributions from the community to help make this project even better. Please feel free to open pull requests or issues. Thank you for considering contributing, and we look forward to collaborating with you!

Below you can find the [plugin development guide](#plugin-development) that will help you get started with running MedusaWP in your local environment.

### Plugin Development

#### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/)
  - We suggest using [OrbStack](https://orbstack.dev/download) on Mac.
- [Node.js v20](https://nodejs.org/en/download/)
  - We suggest using [nvm](https://github.com/nvm-sh/nvm) or [fnm](https://github.com/Schniz/fnm) to manage your Node.js versions.
- [PHP Composer](https://getcomposer.org/download/) latest version 1.x | 2.x

After you have installed the requirements, you will need to add a host entry for the project by appending the following line to your `/etc/hosts` file:
```
127.0.0.1    medusawp.test
```

#### Running Locally

Follow these step-by-step instructions to run the project locally:

1. Fulfill everything mentioned in the prerequisites above
2. `git clone https://github.com/Agilo/medusawp.git` - clone the repo
3. `cd medusawp` - position into the project directory
4. `cp .env.example .env` - set up docker-compose environment variables
5. `cp ./dev/medusa/.env.example ./dev/medusa/.env` - set up Medusa environment variables
6. `npm i` - install all dependencies
7. `npm run addcert -w wordpress` - add a self-signed certificate for `medusawp.test`
8. `npm run build -w wordpress` - build WordPress
9. `composer install -d ./wordpress-plugin` - install Composer dependencies for the WordPress plugin
10. `docker-compose --profile wp --profile medusa up` - start WordPress and Medusa Docker containers
11. Open a new terminal tab
12. `npm run seed -w medusa` - seed Medusa DB
13. `npm run migrate -w medusa` - run Medusa migrations
14. `npm start` - build the Medusa plugin and start the Medusa dev server and Medusa plugin watcher

WordPress is now available at https://medusawp.test and Medusa Admin dashboard at http://localhost:9000/app.

Default credentials for Medusa Admin are:
```
admin@medusa-test.com
supersecret
```

Default credentials for WordPress are:
```
admin
admin
```

#### Available Commands

- `npm start` - build the plugin and start the Medusa dev server and plugin watcher
- `npm run build -w medusa-plugin-wordpress` - build the Medusa plugin
- `npm run watch -w medusa-plugin-wordpress` - start the Medusa plugin watcher
- `npm run test -w medusa-plugin-wordpress` - run Medusa plugin tests
- `npm run seed -w medusa` - seed Medusa DB
- `npm run build -w medusa` - build Medusa
- `npm run migrate -w medusa` - run Medusa migrations
- `npm start -w medusa` - start the Medusa development server
- `npm run addcert -w wordpress` - add a self-signed certificate for `medusawp.test`
- `npm run build -w wordpress` - build WordPress

#### Docker Services

Docker services are defined in `docker-compose.yml` file. There are 2 separate profiles defined in the file:
- `wp` - WordPress
- `medusa` - Medusa

Most of the time, you will want to run both profiles at the same time. But if you want to run only one of them, you can do so by running `docker-compose --profile <profile> up`. For example, to start only WordPress, you would run `docker-compose --profile wp up`.

##### WordPress

- `nginx` - Nginx web server that serves WordPress on https://medusawp.test
- `php` - PHP-FPM
- `db` - MariaDB database server for WordPress available on localhost:3306, you can change credentials and port in `.env` file
- `phpmyadmin` - phpMyAdmin available on http://localhost:8080

##### Medusa

- `postgres` - PostgreSQL database server for Medusa available on localhost:5432, you can change credentials and port in `.env` and `dev/medusa/.env` files
- `pgadmin` - pgAdmin available on http://localhost:5050
- `redis` - Redis server for Medusa available on localhost:6379
- `admin` - Medusa Admin available on http://localhost:9000

## Additional Resources

- [Medusa Documentation](https://docs.medusajs.com/)
- [Medusa Development Documentation](https://docs.medusajs.com/development/overview)
- [WordPress Documentation](https://wordpress.org/documentation/)
- [WordPress Developer Resources](https://developer.wordpress.org/)

## License

This project is licensed under the [MIT License](./LICENSE).

## Credits

MedusaWP is developed and maintained by [AGILO](https://agilo.co/).
Huge thanks to [all contributors](https://github.com/Agilo/medusawp/graphs/contributors).
