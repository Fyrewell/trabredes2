
Trabalho de Redes e Sistemas de Comunicações Móveis 2
============================

Instalação
------------
    Necessita de Apache2.4, PHP5.6, sqlite e composer instalados.
    git clone https://github.com/Fyrewell/trabredes
    cd trabredes
    composer install

    php bin/console doctrine:database:create
    php bin/console doctrine:schema:load

Instalação de pacotes necessários
------------
    sudo apt-get install apache2
    sudo apt-get install php5.6
    sudo apt-get install libapache2-mod-php5.6
    sudo apt-get install php5.6-sqlite

Criação do db da aplicação
------------
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:load
