apt-get -y install openjdk-11-jdk ant php-cli php-json php-xml php-mbstring php-zip  

# The following lines are to make sure we can install packages from maven
# See https://bugs.launchpad.net/ubuntu/+source/ca-certificates-java/+bug/1396760
# and https://github.com/docker-library/openjdk/issues/19#issuecomment-70546872
# apt-get install --reinstall ca-certificates-java
# update-ca-certificates -f
