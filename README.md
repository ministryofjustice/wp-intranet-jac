> [!IMPORTANT]  
> This repository has been archived. In April 2024, the JAC Intranet was relocated to the MoJ Intranet.  
> For the current codebase, see https://github.com/ministryofjustice/intranet/

# JAC Intranet

WordPress project for the Judicial Appointments Commission intranet.

This is based on the template and markup from JAC's old RedDot-powered intranet. Where possible, markup and assets have been 'lifted and shifted' from the old intranet.

## Requirements

* PHP 5.4+
* WordPress 4.3+
* node.js

## Getting Started

To get started with this theme:

1. Setup a fresh install of WordPress.
2. Clone this repo into the same directory as your WordPress installation (usually `htdocs` or `public_html`)
3. In a terminal, change into the directory `wp-content/themes/jacintranet` and run the following commands:
    1. `npm install` – install required node modules
    2. `bower install` – download required bower components
    3. `gulp` – compile static assets from source
4. Enable the theme "JAC Intranet" in the WordPress admin.
5. You will be prompted to install required plugins – install them.
6. Done. You're ready to go.
