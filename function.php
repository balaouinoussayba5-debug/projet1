<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function init_game(): void {
    if (!isset($_SESSION['target'])) {
        $_SESSION['target'] = random_int(0, 100);
        $_SESSION['history'] = [];
        $_SESSION['attempts_used'] = 0;        
        $_SESSION['max_attempts'] = 4;         
        $_SESSION['attempts_remaining'] = 4;   
        $_SESSION['won'] = false;              
    }
}


function reset_game(): void {
    unset($_SESSION['target'], $_SESSION['history'], $_SESSION['attempts_used'], $_SESSION['max_attempts'], $_SESSION['attempts_remaining'], $_SESSION['won']);
    init_game();
}


function submit_guess($raw_guess): array {
    init_game();

    if (!empty($_SESSION['won'])) {
        return ['success' => false, 'message' => 'Vous avez déjà trouvé le nombre — réinitialisez pour rejouer.', 'guess' => null, 'correct' => true, 'remaining' => $_SESSION['attempts_remaining']];
    }

    if (isset($_SESSION['attempts_remaining']) && $_SESSION['attempts_remaining'] <= 0) {
        return ['success' => false, 'message' => 'Plus d\'essais restants — réinitialisez pour rejouer.', 'guess' => null, 'correct' => false, 'remaining' => 0];
    }

    $guess = filter_var($raw_guess, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 100]
    ]);

    if ($guess === false && $guess !== 0) {
        return ['success' => false, 'message' => 'Entrée invalide — entrez un nombre entre 0 et 100.', 'guess' => null, 'correct' => false, 'remaining' => $_SESSION['attempts_remaining']];
    }

    $target = $_SESSION['target'];


    $_SESSION['attempts_used'] = ($_SESSION['attempts_used'] ?? 0) + 1;
    $_SESSION['attempts_remaining'] = max(0, ($_SESSION['attempts_remaining'] ?? $_SESSION['max_attempts']) - 1);

    if ($guess < $target) {
        $msg = 'En plus';
    } elseif ($guess > $target) {
        $msg = 'Au moins';
    } else {
        $msg = "Bravo ! Vous avez trouvé le nombre en {$_SESSION['attempts_used']} essais.";
        $_SESSION['won'] = true;
    }


    $entry = [
        'guess' => $guess,
        'message' => $msg,
        'time' => time(),
        'attempt_no' => $_SESSION['attempts_used']
    ];
    $_SESSION['history'][] = $entry;


    $isCorrect = ($guess === $target);
    if (!$isCorrect && $_SESSION['attempts_remaining'] === 0 && empty($_SESSION['won'])) {
        $msg .= " — Plus d'essais : le nombre était $target.";
    }

    return [
        'success' => true,
        'message' => $msg,
        'guess' => $guess,
        'correct' => $isCorrect,
        'remaining' => $_SESSION['attempts_remaining']
    ];
}

function get_history(): array {
    init_game();
    return $_SESSION['history'] ?? [];
}

function get_attempts_used(): int {
    init_game();
    return $_SESSION['attempts_used'] ?? 0;
}

function get_attempts_remaining(): int {
    init_game();
    return $_SESSION['attempts_remaining'] ?? ($_SESSION['max_attempts'] ?? 4);
}

function debug_target(): int {
    init_game();
    return $_SESSION['target'];
}