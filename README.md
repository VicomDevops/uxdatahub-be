#Install Dependencies
composer install

#Database Config
- Create a new file ".env.local"
- Copy this line to the file: DATABASE_URL=postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=11&charset=utf8
- Change db_user, db_password and db_name with your local informations.

#Database synchro
php bin\console d:d:c
php bin\console d:m:m

#JWT config
- mkdir -p config/jwt
- openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
- openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout