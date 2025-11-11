<?php
require_once __DIR__ . '/function.php';

init_game();

$message = '';
$error = '';
$lastResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        reset_game();
        $message = 'Jeu réinitialisé — un nouveau nombre a été choisi.';
    } elseif (isset($_POST['guess'])) {
        // si plus d'essais ou déjà gagné, submit_guess renverra message adéquat
        $result = submit_guess($_POST['guess']);
        if (!$result['success']) {
            $error = $result['message'];
        } else {
            $lastResult = $result;
            $message = $result['message'];
            if (!empty($result['correct'])) {
                $message = $result['message'] . ' (le nombre était ' . htmlspecialchars($result['guess']) . ')';
            }
        }
    }
}

$history = get_history();
$attempts_used = get_attempts_used();
$attempts_remaining = get_attempts_remaining();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Devine le nombre (0-100) — 4 essais</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; max-width:760px; margin:2rem auto; padding:1rem; }
    input[type="number"] { width:100px; padding:6px; }
    .msg { padding:10px; margin:10px 0; border-radius:6px; }
    .ok { background:#e6ffea; border:1px solid #4caf50; color:#2d6b2d; }
    .warn { background:#fff8e6; border:1px solid #f0ad4e; color:#7d5000; }
    .err { background:#ffecec; border:1px solid #e74c3c; color:#8b1b1b; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th,td { border:1px solid #ddd; padding:8px; text-align:center; }
    th { background:#f5f5f5; }
    .small { font-size:0.9rem; color:#666; }
    .controls { display:flex; gap:8px; align-items:center; margin-top:8px; }
    button { padding:8px 12px; cursor:pointer; }
    .disabled { opacity:0.6; pointer-events:none; }
  </style>
</head>
<body>
  <h1>Devine le nombre (0–100)</h1>
  <p class="small">Tu as <strong><?=htmlspecialchars($attempts_remaining)?></strong> essais restants sur <strong>4</strong>. Je te dirai « <strong>En plus</strong> » si ton essai est trop petit, ou « <strong>Au moins</strong> » s'il est trop grand.</p>

  <?php if ($error): ?>
    <div class="msg err"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>

  <?php if ($message): ?>
    <?php $cls = (!empty($lastResult['correct']) ? 'ok' : (stripos($message, 'En plus') !== false || stripos($message, 'Au moins') !== false ? 'warn' : 'ok')); ?>
    <div class="msg <?=$cls?>"><?=htmlspecialchars($message)?></div>
  <?php endif; ?>

  <?php
    $disableForm = ($attempts_remaining <= 0) || (!empty($_SESSION['won']));
  ?>

  <form method="post" action="">
    <label for="guess">Votre essai :</label>
    <div class="controls <?= $disableForm ? 'disabled' : '' ?>">
      <input type="number" id="guess" name="guess" min="0" max="100" required autocomplete="off" <?= $disableForm ? 'disabled' : '' ?> />
      <button type="submit" <?= $disableForm ? 'disabled' : '' ?>>Envoyer</button>
      <button type="submit" name="reset" value="1" onclick="return confirm('Voulez-vous vraiment réinitialiser le jeu ?')">Réinitialiser</button>
      <button type="button" onclick="document.getElementById('dbg').style.display='block'">Debug</button>
    </div>
  </form>

  <section>
    <h2>Historique (<?=count($history)?> essais)</h2>
    <?php if (empty($history)): ?>
      <p class="small">Pas encore d'essais.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>#</th><th>Essai (n°)</th><th>Valeur</th><th>Réponse</th><th>Heure</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_reverse($history) as $i => $h): ?>
            <tr>
              <td><?= htmlspecialchars(count($history) - $i) ?></td>
              <td><?= htmlspecialchars($h['attempt_no']) ?></td>
              <td><?= htmlspecialchars($h['guess']) ?></td>
              <td><?= htmlspecialchars($h['message']) ?></td>
              <td><?= date('Y-m-d H:i:s', $h['time']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <div id="dbg" style="display:none;margin-top:12px;">
    <h3>Debug (ne pas montrer en prod)</h3>
    <pre class="small">Target (secret) : <?= htmlspecialchars(debug_target()) . PHP_EOL ?>Essais utilisés : <?= htmlspecialchars($attempts_used) . ' / ' . htmlspecialchars($_SESSION['max_attempts']) . PHP_EOL ?>Essais restants : <?= htmlspecialchars($attempts_remaining) ?></pre>
  </div>
</body>
</html>

