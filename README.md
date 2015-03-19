# Twiddit

_It's like twitter, but reddit_

---

Twiddit is a web app that lets you follow Redditors in a way similar to how you
follow people on Twitter.

![demo_image](https://cloud.githubusercontent.com/assets/2539016/6739292/fe919d16-ce34-11e4-8a48-7af8b1327b2e.png)

### Install

```sh
git clone https://github.com/copperwall/twiddit.git
composer install
cp config.example.json config.json

```

Log into MySQL
* Create a database called `twiddit`
* Run `source SQLSchema.sql`

To start the app run

```sh
php -S <your domain>:<port> index.php
```
