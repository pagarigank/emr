scp -r "E:\sithemr\app" sitadmin@192.168.100.201:/var/www/sithemr/
scp -r "E:\sithemr\resources" sitadmin@192.168.100.201:/var/www/sithemr/
scp -r "E:\sithemr\routes" sitadmin@192.168.100.201:/var/www/sithemr/
ssh sitadmin@192.168.100.201 "cd /var/www/sithemr && php artisan optimize:clear && rm -f public/hot"
ssh sitadmin@192.168.100.201 "cd /var/www/sithemr && php artisan optimize:clear && rm -f public/hot && sudo chown -R sitadmin:www-data /var/www/sithemr/app /var/www/sithemr/resources"
Write-Host "Deployment Completed Successfully!" -ForegroundColor Green