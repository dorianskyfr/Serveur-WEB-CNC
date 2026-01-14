<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/lib/auth.php";
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, original_name, file_ext FROM files WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) { http_response_code(404); exit("Fichier introuvable."); }

$ext = strtolower((string)($f['file_ext'] ?? ''));
$isGcode = in_array($ext, ['gcode','nc','ngc','tap','iso','txt'], true);
$isStl = ($ext === 'stl');

$title = "Visionneuse 3D — " . $f['original_name'];

include __DIR__ . "/partials/layout_top.php";
?>
<div class="dashboard-wrapper">
  <?php $active='consultation'; include __DIR__ . "/partials/sidebar.php"; ?>

  <main class="content">
    <h1>Visionneuse 3D</h1>
    <div class="card">
      <p>Fichier : <?= htmlspecialchars($f['original_name']) ?> (<?= htmlspecialchars($ext) ?>)</p>
      <?php if (!$isGcode && !$isStl): ?>
        <div class="alert">Format non supporté par la visionneuse (support: gcode/nc/tap/txt, stl).</div>
      <?php else: ?>
        <div id="viewer" style="width:100%;height:70vh;border:1px solid #333;border-radius:6px;"></div>

        <script type="module">
          import * as THREE from "https://unpkg.com/three@0.160.0/build/three.module.js";
          import { OrbitControls } from "https://unpkg.com/three@0.160.0/examples/jsm/controls/OrbitControls.js";
          import { STLLoader } from "https://unpkg.com/three@0.160.0/examples/jsm/loaders/STLLoader.js";
          import { GCodeLoader } from "https://unpkg.com/three@0.160.0/examples/jsm/loaders/GCodeLoader.js";

          const container = document.getElementById("viewer");

          const scene = new THREE.Scene();
          scene.background = new THREE.Color(0x0f0f14);

          const camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 200000);
          camera.position.set(120, 120, 120);

          const renderer = new THREE.WebGLRenderer({ antialias: true });
          renderer.setSize(container.clientWidth, container.clientHeight);
          container.appendChild(renderer.domElement);

          const controls = new OrbitControls(camera, renderer.domElement);
          controls.enableDamping = true;

          scene.add(new THREE.AmbientLight(0xffffff, 0.6));
          const dir = new THREE.DirectionalLight(0xffffff, 0.8);
          dir.position.set(200, 300, 200);
          scene.add(dir);

          scene.add(new THREE.GridHelper(400, 40, 0x444444, 0x222222));
          scene.add(new THREE.AxesHelper(80));

          function fitCameraToObject(obj) {
            const box = new THREE.Box3().setFromObject(obj);
            const size = box.getSize(new THREE.Vector3()).length();
            const center = box.getCenter(new THREE.Vector3());

            controls.target.copy(center);

            const dist = size * 1.2;
            camera.position.set(center.x + dist, center.y + dist, center.z + dist);
            camera.near = Math.max(0.1, size / 1000);
            camera.far = Math.max(2000, size * 10);
            camera.updateProjectionMatrix();
          }

          const url = "file_raw.php?id=<?= (int)$f['id'] ?>";
          const ext = "<?= htmlspecialchars($ext) ?>";

          if (ext === "stl") {
            const loader = new STLLoader();
            loader.load(url, (geometry) => {
              geometry.computeVertexNormals();
              const material = new THREE.MeshStandardMaterial({ color: 0x00bcd4, metalness: 0.1, roughness: 0.6 });
              const mesh = new THREE.Mesh(geometry, material);
              scene.add(mesh);
              fitCameraToObject(mesh);
            });
          } else {
            const loader = new GCodeLoader();
            loader.load(url, (obj) => {
              scene.add(obj);
              fitCameraToObject(obj);
            });
          }

          function onResize() {
            const w = container.clientWidth;
            const h = container.clientHeight;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
          }
          window.addEventListener("resize", onResize);

          function animate() {
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
