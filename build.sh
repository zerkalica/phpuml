#!/bin/sh

BUILD_DIR="$(dirname $0)/src"


extract() {
    local name="$1"
    local dest="$2"
    wget -c "http://download.pear.php.net/package/$name.tgz" -O $name.tgz
    tar xfz $name.tgz && mv $name $dest && rm $name.tgz
}

rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR/PEAR
    cd $BUILD_DIR && \
    extract "PHP_UML-1.6.1" "PHP" && \
    extract "Console_CommandLine-1.2.0" "console-commandline" && \
    wget "https://raw.github.com/pear/pear-core/master/PEAR/Exception.php" -O "PEAR/Exception.php" && \
    sed "/\#\!\@php_bin\@/d" -i "PHP/scripts/phpuml"
