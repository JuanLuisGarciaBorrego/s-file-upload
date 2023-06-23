# Documentación de la API

## Install

- PHP 8.1 with ffi.enable = true (php.ini)
- Mysql or MariaDB
- CaddyFile (/etc/caddy/Caddyfile)

```
{{api_server_name}} {
    root * {{api_project_public_dir}}
    #php_fastcgi unix//run/php/php{{php_version}}-fpm.sock
    php_fastcgi unix//run/php/php-libvips-fpm.sock
    encode zstd gzip
    file_server

    log {
        output file /var/log/caddy/{{api_server_name}}_access.log
        level ERROR
    }
}
```

- Create BBDD

```bin/console doctrine:database:create```
```bin/console doctrine:schema:update --force```
