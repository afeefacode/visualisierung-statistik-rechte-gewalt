# visualisierung-statistik-rechte-gewalt
Visualisierung Statistik rechter Gewalttaten


## Update example

```
npm run build
cp dist/app.umd.min.js example/visualisierung-statistik.js
cp dist/app.css example/visualisierung-statistik.css
```

## Update plugin

```
npm run build
cp dist/app.umd.min.js wp-plugin/visualisierung-statistik/visualisierung-statistik.js
cp dist/app.css wp-plugin/visualisierung-statistik/visualisierung-statistik.css
cp example/vue.min.js wp-plugin/visualisierung-statistik/vue.min.js

cd wp-plugin
zip -r visualisierung-statistik.zip visualisierung-statistik
```
