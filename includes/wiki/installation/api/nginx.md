Nginx
=====

API virtualhost
---------------

    server {
        listen 443;
        server_name api.apiopenstudio.local;
        index index.php;
        error_log /var/log/nginx/error.log debug;
        access_log /var/log/nginx/access.log;
        root /var/www/html/api/public;
    
        location ~ ^/(?!(index.php|sample)) {
            rewrite ^/(.*)$ /index.php?request=$1 last;
        }
    
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            fastcgi_index index.php;
            fastcgi_pass php:9000;
        }
    
        location ~ /\.ht {
            deny all;
        }
    }