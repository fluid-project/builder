# Runs the Infusion Builder deployment steps documented at http://wiki.fluidproject.org/display/fluid/Deploying+the+Infusion+Builder

# Prompt the user for the database coordinates.
echo -n "Please enter the Infusion Builder database username: "
read username
echo -n "and the database password: "
read password

# Export the Builder from SVN
svn export https://source.fluidproject.org/svn/incubator/infusionBuilder/trunk/ infusionBuilder
cd infusionBuilder/infusionBuilder-deploy

# Run the Ant deploy script to ensure the correct directory structure and caching is in place
ant -Dmysql_user=$username -Dmysql_password=$password
cd ~

# Clean up
rm -rf infusionBuilder

