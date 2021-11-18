# SymfonyProject
 1- Open terminal and change the cwd to the folder where Composer.json is located, all the terminal commands to execute are done in this folder, if you are located in a different folder, please update the commands with the right paths

 2- Execute the following commands:
 
    php ../composer.phar update

 3- If you are deploying the application in a localhost, we recommand to install wampserver. 

 4- Make sure to give the right access to the database by modifiying the file ./app/config/parameters.yml and then continue with the following commands - fill all the informations in parameters. yml file
    -$php bin/console doctrine:database:create
    -$php bin/console doctrine:schema:update --force
    
 5- Deploy the application on the server, you can use a dev environment by using the following command, or deploy a prod environment by hosting the app with defining web/ as the root app directory
	-$php bin/console server:start

	
Please make sure you are connected to internet since it uses bootstrap ressources
