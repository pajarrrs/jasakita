#!/usr/bin/env bash
#
# Build APK Android JasaKita tanpa Gradle (langsung ke aapt2 + d8 + apksigner).
#
# Pemakaian:
#   ./build.sh                        # alamat server default di dalam APK
#   ./build.sh http://10.0.0.5:8000   # ganti alamat server default
#
# Hasil build: storage/app/public/apk/app-jasa.apk dan app-mitra.apk
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT="$(cd "$ROOT/../.." && pwd)"

SDK="${ANDROID_HOME:-$HOME/Library/Android/sdk}"
BUILD_TOOLS="$(ls -d "$SDK"/build-tools/* | sort -V | tail -1)"
PLATFORM_JAR="$(ls -d "$SDK"/platforms/android-* | sort -V | tail -1)/android.jar"

if [ -z "${JAVA_HOME:-}" ] && [ -d "/Applications/Android Studio.app/Contents/jbr/Contents/Home" ]; then
    JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home"
fi

export JAVA_HOME
JAVAC="$JAVA_HOME/bin/javac"
KEYTOOL="$JAVA_HOME/bin/keytool"

SERVER_URL="${1:-http://192.168.110.11:8000}"
OUT_DIR="$PROJECT/storage/app/public/apk"
WORK="$ROOT/.build"
KEYSTORE="$ROOT/keystore.jks"
KS_PASS="jasakita"

echo "SDK          : $SDK"
echo "Build tools  : $BUILD_TOOLS"
echo "Alamat server: $SERVER_URL"

mkdir -p "$OUT_DIR" "$WORK"

if [ ! -f "$KEYSTORE" ]; then
    echo "Membuat keystore baru..."
    "$KEYTOOL" -genkeypair -v -keystore "$KEYSTORE" -alias jasakita \
        -keyalg RSA -keysize 2048 -validity 10000 \
        -storepass "$KS_PASS" -keypass "$KS_PASS" \
        -dname "CN=JasaKita, OU=Mobile, O=JasaKita, L=Bandung, C=ID" >/dev/null 2>&1
fi

build_variant() {
    local slug="$1" package="$2" app_name="$3" label="$4" color="$5" out_name="$6"
    local dir="$WORK/$slug"

    rm -rf "$dir"
    mkdir -p "$dir/res"
    cp -R "$ROOT/app/res/." "$dir/res/"

    php "$ROOT/tools/make-icons.php" "$dir/res" "$label" "$color" >/dev/null

    cat > "$dir/res/values/variant.xml" <<XML
<?xml version="1.0" encoding="utf-8"?>
<resources>
    <string name="app_name">$app_name</string>
    <string name="default_server_url">$SERVER_URL</string>
    <string name="start_path">/masuk</string>
    <color name="brand">$color</color>
</resources>
XML

    sed "s/__PACKAGE__/$package/" "$ROOT/app/AndroidManifest.xml.template" > "$dir/AndroidManifest.xml"

    echo "[$slug] kompilasi resource..."
    "$BUILD_TOOLS/aapt2" compile --dir "$dir/res" -o "$dir/res.flata" >/dev/null

    "$BUILD_TOOLS/aapt2" link -o "$dir/base.apk" \
        -I "$PLATFORM_JAR" \
        --manifest "$dir/AndroidManifest.xml" \
        --java "$dir/gen" \
        --custom-package id.jasakita.app \
        --min-sdk-version 26 \
        --target-sdk-version 36 \
        --version-code 1 \
        --version-name "1.0" \
        "$dir/res.flata" >/dev/null

    echo "[$slug] kompilasi kode Java..."
    mkdir -p "$dir/classes"
    "$JAVAC" -nowarn -source 8 -target 8 \
        -bootclasspath "$PLATFORM_JAR" \
        -classpath "$PLATFORM_JAR" \
        -d "$dir/classes" \
        $(find "$ROOT/app/src" "$dir/gen" -name "*.java")

    echo "[$slug] membungkus dex..."
    mkdir -p "$dir/dex"
    "$BUILD_TOOLS/d8" --release --lib "$PLATFORM_JAR" --min-api 26 \
        --output "$dir/dex" $(find "$dir/classes" -name "*.class") >/dev/null

    (cd "$dir/dex" && zip -q "$dir/base.apk" classes.dex)

    "$BUILD_TOOLS/zipalign" -f 4 "$dir/base.apk" "$dir/aligned.apk"

    "$BUILD_TOOLS/apksigner" sign \
        --ks "$KEYSTORE" \
        --ks-pass "pass:$KS_PASS" \
        --key-pass "pass:$KS_PASS" \
        --out "$OUT_DIR/$out_name" \
        "$dir/aligned.apk"

    rm -f "$OUT_DIR/$out_name.idsig"

    echo "[$slug] selesai -> storage/app/public/apk/$out_name ($(du -h "$OUT_DIR/$out_name" | cut -f1))"
}

build_variant "pelanggan" "id.jasakita.app" "JasaKita" "JK" "#0D9488" "app-jasa.apk"
build_variant "mitra" "id.jasakita.mitra" "JasaKita Mitra" "M" "#047857" "app-mitra.apk"

echo "Selesai. Dua APK siap diunduh dari halaman /unduh."
