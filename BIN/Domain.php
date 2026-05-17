
REMOVE ALL EXISTING 

sudo rm -rf /var/www/taongaf.com /var/www/taongaf /etc/nginx/sites-available/taongaf.com /etc/nginx/sites-available/taongaf /etc/nginx/sites-enabled/taongaf.com /etc/nginx/sites-enabled/taongaf



 MAKE NEW ONES
sudo mkdir -p /var/www/taongaf.com 

rsync -avzP --exclude='node_modules' --exclude='storage/*.php' --exclude='storage/framework/cache/data/*' ./ root@63.250.47.120:/var/www/taongaf.com




nano /etc/nginx/sites-available/taongaf.com
server {
    listen 80;
    server_name taongaf.com www.taongaf.com;
    root /var/www/://taongaf.com;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}



ln -sf /etc/nginx/sites-available/taongaf.com /etc/nginx/sites-enabled/ && nginx -t










chown -R www-data:www-data /var/www/taongaf.com  

Login
ssh root@63.250.47.120
0Xa7lQm283iOsRy7ZV



sudo mkdir -p /var/www/taongaf.com && sudo chown $USER:$USER /var/www/taongaf.com

rsync -avzP --exclude='node_modules' --exclude='storage/*.php' --exclude='storage/framework/cache/data/*' ./ user@63.250.47.120:/var/www/taongaf.com








rsync -avzP --exclude='.git' --exclude='.env' --exclude='node_modules' --exclude='vendor' ./ user@YOUR_SERVER_IP:/var/www/taongaf.com


sudo rm -rf /var/www/taongaf.com /var/www/taongaf /etc/nginx/sites-available/taongaf.com /etc/nginx/sites-available/taongaf /etc/nginx/sites-enabled/taongaf.com /etc/nginx/sites-enabled/taongaf






rsync -avzP --exclude='node_modules' --exclude='storage/*.php' --exclude='storage/framework/cache/data/*' ./ user@YOUR_SERVER_IP:/var/www/taongaf.com








sudo mkdir -p /var/www/taongaf

nano /etc/nginx/sites-available/taongaf


ln -s /etc/nginx/sites-available/taongaf /etc/nginx/sites-enabled/


sudo chown -R www-data:www-data /var/www/taongaf.com and sudo chmod -R 775 /var/www/://taongaf


rsync -avz --progress ./ root@63.250.47.120:/var/www/taongaf/

#rsync -avz --exclude vendor --exclude node_modules ./ #root@63.250.47.120:/var/www/taongaf/










sudo mkdir -p /var/www/taongaf.com 
sudo chown -R $USER:$USER /var/www/taongaf.com






chown -R www-data:www-data /var/www/taongaf.com  
chmod -R 775 /var/www/taongaf.com/storage /var/www/://taongaf.com  
ln -sf /etc/nginx/sites-available/taongaf.com /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx



rsync -avzP 
      --exclude='.git' 
      --exclude='.env' 
      --exclude='node_modules' 
      --exclude='storage' ./ root@63.250.47.120:/var/www/taongaf/


