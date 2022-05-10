# visualisierung-statistik-rechte-gewalt
Visualisierung Statistik rechter Gewalttaten


## Update example

```
npm run build
cp dist/app.umd.min.js example/visualisierung-statistik.js
cp dist/app.css example/visualisierung-statistik.css
```

## Update wordpress plugin

```
npm run build
cp dist/app.umd.min.js wp-plugin/visualisierung-statistik/visualisierung-statistik.js
cp dist/app.css wp-plugin/visualisierung-statistik/visualisierung-statistik.css
cp example/vue.min.js wp-plugin/visualisierung-statistik/vue.min.js

cd wp-plugin
zip -r visualisierung-statistik.zip visualisierung-statistik
```

## Dev mode wordpress

1. Start vue project

```
npm run dev
```

Find and copy service port number:

```
  App running at:
  - Local:   http://localhost:8081/vue/ <-- port is 8081
  - Network: http://192.168.2.104:8081/vue/
```

2. Start wordpress docker

```
cd wp-docker
docker-compose up
```

Put shortcode `[Statistik-Visualisierung]` in a random page or post, save and visit page or post.

3. Enable wp-plugin dev mode:

This will use the vue dev server sources instead the build sources from the plugin folder:

```
code wp-plugin/visualisierung-statistik/visualisierung-statistik.php
...
define('VISUALISIERUNG_DEV', false); <-- set to true
define('VISUALISIERUNG_DEV_PORT', 8081); <-- set to port from npm run dev

```
