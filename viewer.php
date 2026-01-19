<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, original_name, file_ext FROM files WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$f) { http_response_code(404); exit("Fichier introuvable."); }

$ext = strtolower((string)($f['file_ext'] ?? ''));
$isGcode = in_array($ext, ['gcode','nc','ngc','tap','iso','txt','cammgl'], true);
$isStl   = ($ext === 'stl');

$title = "Visionneuse 3D — " . (string)$f['original_name'];
include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active='consultation'; include __DIR__ . "/partials/sidebar.php"; ?>

  <main class="content">
    <h1>Visionneuse 3D</h1>

    <div class="card">
      <p>Fichier : <?= htmlspecialchars((string)$f['original_name']) ?> (<?= htmlspecialchars($ext) ?>)</p>

      <?php if (!$isGcode && !$isStl): ?>
        <div class="alert">Format non supporté (support : gcode/nc/ngc/tap/iso/txt/cammgl, stl).</div>
      <?php else: ?>

        <style>
          .sidebar{ display:none !important; }
          .content{ width:100% !important; max-width:none !important; }

          .viewer-toolbar{
            display:flex; gap:10px; flex-wrap:wrap;
            margin:10px 0 12px;
          }
          .viewer-toolbar button{
            padding:8px 12px; border-radius:6px;
            border:1px solid #333; background:#15151a; color:#e8e8ee;
            cursor:pointer;
          }
          #rot-info{ opacity:.85; align-self:center; margin-left:10px; }

          #viewer{
            width:100%;
            height: calc(100vh - 170px);
            min-height: 820px;
            border:1px solid #333;
            border-radius:6px;
            overflow:hidden;
          }
        </style>

        <div class="viewer-toolbar">
          <button type="button" id="btn-fit">Ajuster (fit)</button>
          <button type="button" id="btn-full">Plein écran</button>

          <button type="button" id="btn-preset-0">Preset: 0</button>
          <button type="button" id="btn-preset-a">Preset: A</button>
          <button type="button" id="btn-preset-b">Preset: B</button>

          <button type="button" id="btn-rx">X +90°</button>
          <button type="button" id="btn-ry">Y +90°</button>
          <button type="button" id="btn-rz">Z +90°</button>

          <button type="button" id="btn-rot-clear">Reset rotation</button>
          <span id="rot-info"></span>
        </div>

        <div id="viewer"></div>

        <script type="importmap">
        {
          "imports": {
            "three": "./assets/three.js-r182/build/three.module.js",
            "three/examples/jsm/": "./assets/three.js-r182/examples/jsm/"
          }
        }
        </script>

        <script type="module">
          import * as THREE from "three";
          import { OrbitControls } from "three/examples/jsm/controls/OrbitControls.js";
          import { STLLoader } from "three/examples/jsm/loaders/STLLoader.js";
          import { GCodeLoader } from "three/examples/jsm/loaders/GCodeLoader.js";

          const container = document.getElementById("viewer");

          const scene = new THREE.Scene();
          scene.background = new THREE.Color(0x0f0f14);

          const camera = new THREE.PerspectiveCamera(
            60,
            container.clientWidth / container.clientHeight,
            0.1,
            200000
          );
          camera.position.set(120, 120, 120);

          const renderer = new THREE.WebGLRenderer({ antialias: true });
          renderer.setSize(container.clientWidth, container.clientHeight);
          renderer.setPixelRatio(window.devicePixelRatio);
          container.appendChild(renderer.domElement);

          const controls = new OrbitControls(camera, renderer.domElement);
          controls.enableDamping = true;

          scene.add(new THREE.AmbientLight(0xffffff, 0.65));
          const dir = new THREE.DirectionalLight(0xffffff, 0.85);
          dir.position.set(200, 300, 200);
          scene.add(dir);

          scene.add(new THREE.GridHelper(600, 60, 0x444444, 0x222222));
          scene.add(new THREE.AxesHelper(120));

          const fileId = <?= (int)$f['id'] ?>;
          const rotKey = "stl_rot_" + fileId;
          const rotInfo = document.getElementById("rot-info");

          let loadedObject = null;

          function radToDeg(r){ return Math.round(r * 180 / Math.PI); }
          function updateRotInfo(){
            if (!loadedObject || !rotInfo) return;
            rotInfo.textContent =
              `Rotation: X ${radToDeg(loadedObject.rotation.x)}° / Y ${radToDeg(loadedObject.rotation.y)}° / Z ${radToDeg(loadedObject.rotation.z)}°`;
          }

          function saveRot(){
            if (!loadedObject) return;
            localStorage.setItem(rotKey, JSON.stringify([
              loadedObject.rotation.x,
              loadedObject.rotation.y,
              loadedObject.rotation.z
            ]));
            updateRotInfo();
          }

          function loadRot(obj){
            const saved = localStorage.getItem(rotKey);
            if (!saved) return false;
            try {
              const [x,y,z] = JSON.parse(saved);
              obj.rotation.set(x,y,z);
              return true;
            } catch(e) {
              return false;
            }
          }

          // Recale: centre X/Z et pose le bas sur y=0 (grille)
          function normalizeOnFloorAndCenterXZ(obj){
            // IMPORTANT: repartir d’une position neutre, sinon ça “dérive” à chaque clic
            obj.position.set(0,0,0);
            obj.updateWorldMatrix(true, true);

            const box = new THREE.Box3().setFromObject(obj);
            const center = box.getCenter(new THREE.Vector3());

            // met le centre au-dessus de (0,*,0)
            obj.position.x = -center.x;
            obj.position.z = -center.z;

            // pose le bas sur y=0
            obj.position.y = -box.min.y;

            obj.updateWorldMatrix(true, true);
          }

          function fitCameraToObject(obj){
            obj.updateWorldMatrix(true, true);
            const box = new THREE.Box3().setFromObject(obj);
            const size = box.getSize(new THREE.Vector3()).length();
            const center = box.getCenter(new THREE.Vector3());

            controls.target.copy(center);

            const dist = Math.max(250, size * 1.2);
            camera.position.set(center.x + dist, center.y + dist, center.z + dist);
            camera.near = Math.max(0.1, size / 1000);
            camera.far = Math.max(3000, size * 10);
            camera.updateProjectionMatrix();
          }

          function showError(msg){
            const div = document.createElement("div");
            div.className = "alert";
            div.textContent = msg;
            container.replaceWith(div);
          }

          const url = "./file_raw.php?id=<?= (int)$f['id'] ?>";
          const ext = <?= json_encode($ext) ?>;

          // Presets
          const PRESET_0 = new THREE.Euler(0, 0, 0);
          const PRESET_A = new THREE.Euler(-Math.PI/2, 0, 0);
          const PRESET_B = new THREE.Euler(-Math.PI/2, 0, Math.PI);

          function applyPreset(euler){
            if (!loadedObject) return;
            loadedObject.rotation.copy(euler);
            normalizeOnFloorAndCenterXZ(loadedObject);
            fitCameraToObject(loadedObject);
            saveRot();
          }

          if (ext === "stl") {
            const loader = new STLLoader();
            loader.load(url, (geometry) => {
              geometry.computeVertexNormals();

              const material = new THREE.MeshStandardMaterial({
                color: 0x00bcd4, metalness: 0.1, roughness: 0.6
              });

              const mesh = new THREE.Mesh(geometry, material);
              loadedObject = mesh;
              scene.add(mesh);

              // rotation sauvegardée si existe, sinon preset B
              if (!loadRot(mesh)) mesh.rotation.copy(PRESET_B);

              normalizeOnFloorAndCenterXZ(mesh);
              fitCameraToObject(mesh);
              updateRotInfo();
            }, undefined, (e) => {
              console.error(e);
              showError("Erreur chargement STL (voir console F12).");
            });
          } else {
            const loader = new GCodeLoader();
            loader.load(url, (obj) => {
              loadedObject = obj;
              scene.add(obj);

              normalizeOnFloorAndCenterXZ(obj);
              fitCameraToObject(obj);
              updateRotInfo();
            }, undefined, (e) => {
              console.error(e);
              showError("Erreur chargement G-code (voir console F12).");
            });
          }

          // Boutons
          document.getElementById("btn-fit").addEventListener("click", () => {
            if (!loadedObject) return;
            normalizeOnFloorAndCenterXZ(loadedObject);
            fitCameraToObject(loadedObject);
            updateRotInfo();
          });

          document.getElementById("btn-full").addEventListener("click", () => {
            if (container.requestFullscreen) container.requestFullscreen();
          });

          document.getElementById("btn-preset-0").addEventListener("click", () => applyPreset(PRESET_0));
          document.getElementById("btn-preset-a").addEventListener("click", () => applyPreset(PRESET_A));
          document.getElementById("btn-preset-b").addEventListener("click", () => applyPreset(PRESET_B));

          function rot(axis){
            if (!loadedObject) return;
            loadedObject.rotation[axis] += Math.PI / 2;
            normalizeOnFloorAndCenterXZ(loadedObject);
            fitCameraToObject(loadedObject);
            saveRot();
          }
          document.getElementById("btn-rx").addEventListener("click", () => rot("x"));
          document.getElementById("btn-ry").addEventListener("click", () => rot("y"));
          document.getElementById("btn-rz").addEventListener("click", () => rot("z"));

          document.getElementById("btn-rot-clear").addEventListener("click", () => {
            if (!loadedObject) return;
            localStorage.removeItem(rotKey);
            loadedObject.rotation.copy(PRESET_B);
            normalizeOnFloorAndCenterXZ(loadedObject);
            fitCameraToObject(loadedObject);
            updateRotInfo();
          });

          function onResize(){
            const w = container.clientWidth;
            const h = container.clientHeight;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
          }
          window.addEventListener("resize", onResize);

          function animate(){
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
          }
          animate();
        </script>

      <?php endif; ?>
    </div>
  </main>
</div>
<?php include __DIR__ . "/partials/layout_bottom.php"; ?>
