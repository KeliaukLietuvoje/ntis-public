# NTIS Public

[![License](https://img.shields.io/github/license/KeliaukLietuvoje/ntis-public)](https://github.com/KeliaukLietuvoje/ntis-public/blob/main/LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/KeliaukLietuvoje/ntis-public)](https://github.com/KeliaukLietuvoje/ntis-public/issues)
[![GitHub stars](https://img.shields.io/github/stars/KeliaukLietuvoje/ntis-public)](https://github.com/KeliaukLietuvoje/ntis-public/stargazers)

This repository contains the source code and documentation for the NTIS Public, developed by the Keliauk Lietuvoje.

## Table of Contents

- [About the Project](#about-the-project)
- [Getting Started](#getting-started)
    - [Installation](#installation)
    - [Usage](#usage)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## About the Project

## Getting Started

To get started with the TIP Public, follow the instructions below.

### Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/KeliaukLietuvoje/ntis-public.git
   ```

2. Install the required dependencies:

   ```bash
   cd ntis-public
   composer install
   ```
3. Rename .env.example to.env and copy Env Format salts generated at `https://roots.io/salts.html`

### Usage

1. Start the WEB server:

   ```bash
   npm run dc:up
   ```

The WEB will be available at `http://localhost`.

2. Install Wordpress.

3. Login to MySQL database via Adminer available at `http://localhost:8888`

    host: mariadb
    dbname: mariadb
    dbuser: mariadb
    password: mariadb

4. Login to MinIO dashboard available at `http://localhost:9931`

    user: minioadmin
    pass: minioadmin

    Bucket access permission: Public

## Deployment

### Production

To deploy the application to the production environment, create a new GitHub release:

1. Go to the repository's main page on GitHub.
2. Click on the "Releases" tab.
3. Click on the "Create a new release" button.
4. Provide a version number, such as `1.2.3`, and other relevant information.
5. Click on the "Publish release" button.

### Staging

The `main` branch of the repository is automatically deployed to the staging environment. Any changes pushed to the main
branch will trigger a new deployment.

## Contributing

Contributions are welcome! If you find any issues or have suggestions for improvements, please open an issue or submit a
pull request. For more information, see the [contribution guidelines](./CONTRIBUTING.md).

## License

This project is licensed under the [MIT License](./LICENSE).