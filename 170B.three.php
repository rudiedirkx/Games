<?php
// MAHJONG MAP BUILDER

require __DIR__ . '/inc.bootstrap.php';

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MAHJONG MAP BUILDER</title>
<style>
* { margin: 0; padding: 0; }
html, body, canvas { display: block; }
canvas.pointing { cursor: pointer; }
</style>
</head>

<body>

<script src="//home.hotblocks.nl/tests/three/three-82.js"></script>
<script src="//home.hotblocks.nl/tests/three/Rudie.Three.js"></script>
<script src="170.js"></script>
<script>
var renderer = new THREE.WebGLRenderer({antialias: true});
renderer.setClearColor(new THREE.Color(0xEEEEEE, 1.0));
renderer.clear();
renderer.setSize(innerWidth, innerHeight);
document.body.appendChild(renderer.domElement);

var scene = new THREE.Scene();

var camera = new THREE.PerspectiveCamera(45, innerWidth / innerHeight, 1, 10000);
camera.position.x = -50;
camera.position.y = 500;
camera.position.z = 250;
scene.add(camera);
camera.lookAt(scene.position);

scene.makeAxes(5000);

var light = new THREE.PointLight(0xFFFFFF);
light.position.set(0, 200, 0);
light.intensity = 1;
scene.add(light);

var container = new THREE.Object3D;
scene.add(container);

renderer.addDragRotation(scene, camera);
renderer.addScrollZoom(camera);
renderer.addHoverPointer(camera, container);

renderer.keepRendering(scene, camera);

// ==== //

var tileGeo = new THREE.CubeGeometry(40, 10, 60);
// console.log(tileGeo);
var tileColor = new THREE.MeshLambertMaterial({color: 0xFFFFFF});

function createTile() {
	var obj = new THREE.Mesh(tileGeo, tileColor);
	return obj;
}

function addTile(x, y, z) {
	var tile = createTile();
	tile.position.set(x+20, y+5, z+30);
	container.add(tile);
}

addTile(0, 0, 0);
</script>

</body>

</html>
