TEMP=`mktemp -d`
svn co https://plugins.svn.wordpress.org/$1/ $TEMP/wp-svn/
git archive -o $TEMP/wp-archive.zip $2
unzip -o $TEMP/wp-archive.zip -d $TEMP/wp-archive/
rsync -avzh $TEMP/wp-archive/ $TEMP/wp-svn/trunk/
rsync -avzh $TEMP/wp-archive/ $TEMP/wp-svn/tags/$2/
svn commit -m "Import $2 from `git config --get remote.origin.url`" $TEMP/wp-svn/
