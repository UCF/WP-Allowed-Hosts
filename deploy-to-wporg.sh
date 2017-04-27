# In your Jenkins job configuration, select "Add build step > Execute shell", and paste this script contents.
# Replace `______your-plugin-name______`, `______your-wp-username______` and `______your-wp-password______` as needed.


# main config
# WP_ORG_USER="______your-wp-username______" # WordPress.org username - Provided via Jenkins Job
# WP_ORG_PASS="______your-wp-password______" # your WordPress.org password - Provided via Jenkins Job
# WP_ORG_USER="${1}"
# set +x
# WP_ORG_PASS="${2}"
# set -x
#PLUGINSLUG="wp-allow-hosts"
PLUGINSLUG="testing-svn-sync-with-git-code"
CURRENTDIR=`pwd`
MAINFILE="allowed-hosts.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR" # this file should be in the base of your git repository

# svn config
TMP_PATH=`mktemp -d`
SVNPATH="$TMP_PATH/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
#SVNURL="https://plugins.svn.wordpress.org/$PLUGINSLUG/" # Remote SVN repo on wordpress.org, with no trailing slash
SVNURL="https://svn.code.sf.net/p/testing-svn-sync-with-git/code/"
COMMITMSG="Deploy to WordPress.org via Jenkins"


# Let's begin...
echo ".........................................."
echo 
echo "Preparing to deploy wordpress plugin"
echo 
echo ".........................................."
echo

# Check version in readme.txt is the same as plugin file
NEWVERSION1=`grep "^Stable tag" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`
echo "readme version: $NEWVERSION1"
NEWVERSION2=`grep "Version" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then echo "Versions don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and PHP file. Let's proceed..."
COMMITMSG="Deploy ${PLUGINSLUG} ${NEWVERSION1} to WordPress.org via Jenkins"
echo 
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Ignoring github specific & deployment script"
svn propset svn:ignore "deploy-to-wporg.sh
.git
.gitignore" "$SVNPATH/trunk/"

if [ ! -d "$SVNPATH/assets/" ]; then
	echo "Moving assets-wp-repo"
	mkdir $SVNPATH/assets/
	mv $SVNPATH/trunk/assets-wp-repo/* $SVNPATH/assets/
	svn add $SVNPATH/assets/
	svn delete $SVNPATH/trunk/assets-wp-repo
fi

echo "Changing directory to SVN"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
echo "committing to trunk"
set +x
echo 'svn commit --username=$WP_ORG_USER --password=$WP_ORG_PASS -m "$COMMITMSG"'
echo `svn commit --username=$WP_ORG_USER --password=$WP_ORG_PASS -m "$COMMITMSG"`
set -x
echo "Check if tagged version exists"
cd $SVNPATH
if [ ! -d "$SVNPATH/tags/$NEWVERSION1/" ]; then
	echo "Creating new SVN tag & committing it"
	svn copy trunk/ tags/$NEWVERSION1/
	cd $SVNPATH/tags/$NEWVERSION1
	set +x
	echo 'svn commit --username=$WP_ORG_USER --password=$WP_ORG_PASS -m "Tagging version $NEWVERSION1"'
	echo `svn commit --username=$WP_ORG_USER --password=$WP_ORG_PASS -m "Tagging version $NEWVERSION1"`
	set -x
fi

cd $GITPATH
echo "Removing temporary directory $SVNPATH"
rm -fr $TMP_PATH/

echo "*** FIN ***"