RED='\e[1;31m'
GREEN='\e[1;32m'
BLUE='\e[1;36m'
UNSET='\e[0m'

# verififer si le fichier .env.local existe
if [ ! -f .env.local ]
then
    echo -e "";
    echo -e "${RED}###############################";
    echo -e "#   .env.local file missing   #";
    echo -e "###############################${UNSET}";
    exit 1
fi

# Set ENV
ENV=$(grep APP_ENV= .env.local | cut -d '=' -f2)

echo -e "";
echo -e "${BLUE}// Server Environment is : ${GREEN}$ENV${UNSET}";
echo -e "";

sleep 2;

echo -e "${BLUE}##############################";
echo -e "#   Clearing Symfony Cache   #";
echo -e "##############################${UNSET}";
rm -rf var/cache/$ENV/*
echo -e Done.

echo -e "";
echo -e "${BLUE}##############################";
echo -e "#      Composer Install      #";
echo -e "##############################${UNSET}";
if [ "$ENV" = "prod" ]
then
    echo -e "${BLUE}// Installing without dev-dependencies / with optimize in prod ENV ${UNSET}";
    COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-dev
else
    echo -e "${BLUE}// Installing with dev-dependencies / without optimize in dev ENV ${UNSET}";
    COMPOSER_MEMORY_LIMIT=-1 composer install --prefer-dist
fi

echo -e "";
echo -e "${BLUE}##############################";
echo -e "#     YARN Install     #";
echo -e "##############################${UNSET}";
yarn install


echo -e "";
echo -e "${BLUE}##########################";
echo -e "#     Assets Install     #";
echo -e "##########################${UNSET}";
php bin/console assets:install



echo -e "";
echo -e "${BLUE}##########################";
echo -e "#   Building Front-end   #";
echo -e "##########################${UNSET}";
# fixtures
# php -d memory_limit=-1 bin/console doctrine:fixtures:load --no-interaction --append
case "$ENV" in
prod)
    echo -e "${BLUE}// Building without dev-dependencies / with optimize in prod ENV ${UNSET}";
    yarn encore production
    ;;
*)
    echo -e "${BLUE}// Building  with dev-dependencies / without optimize in dev ENV ${UNSET}";
    yarn encore dev
    ;;
esac


echo -e "";
echo -e "${BLUE}##############################";
echo -e "#  Clearing Doctrine Cache   #";
echo -e "##############################${UNSET}";
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result

echo -e "";
echo -e "${BLUE}##############################";
echo -e "#   Checking MySQL Schema    #";
echo -e "##############################${UNSET}";
php bin/console doctrine:database:create --if-not-exists --no-interaction
php -d memory_limit=-1 bin/console doctrine:migrations:migrate --no-interaction
php -d memory_limit=-1 bin/console snowtricks:load-fixtures --no-interaction

echo -e "";
echo -e "${BLUE}##############################";
echo -e "#    Setting permissions             #";
echo -e "##############################${UNSET}";

chown -R www-data:www-data public/uploads/
chown -R www-data:www-data var/


chmod -R 777 var/*
chmod -R 777 public/uploads/*

echo -e "";
echo -e "${BLUE}##############################";
echo -e "#             Done                   #";
echo -e "##############################${UNSET}";
