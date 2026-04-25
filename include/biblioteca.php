<?php

if (!function_exists('generate_game_key')) {


// Gera uma chave aleatória após a compra de um jogo
  function generate_game_key(): string
  {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $pick = function (int $n) use ($alphabet) {
      $s = '';
      for ($i = 0; $i < $n; $i++) {
        $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
      }
      return $s;
    };
    return $pick(5) . '-' . $pick(5) . '-' . $pick(5) . '-' . $pick(5);
  }
}


// Verifica se o usuário possui ao menos 1 UMA chave comprada de determinado jogo
if (!function_exists('library_has')) {
  function library_has(mysqli $conn, int $userId, int $jogoId): bool
  {
    $st = $conn->prepare('SELECT 1 FROM biblioteca WHERE user_id=? AND jogo_id=? LIMIT 1');
    $st->bind_param('ii', $userId, $jogoId);
    $st->execute();
    return (bool) $st->get_result()->fetch_row();
  }
}


// Caso haja mais de uma, retorna todas as chaves que aquele usuário possui do jogo
if (!function_exists('library_get_keys')) {
  function library_get_keys(mysqli $conn, int $userId, int $jogoId): array
  {
    $st = $conn->prepare('SELECT chave FROM biblioteca WHERE user_id=? AND jogo_id=? ORDER BY id ASC');
    $st->bind_param('ii', $userId, $jogoId);
    $st->execute();
    return array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'chave');
  }
}


// Guarda o data e hora da compra
if (!function_exists('library_add')) {
  function library_add(mysqli $conn, int $userId, int $jogoId): string
  {
    $now = date('Y-m-d H:i:s');

    // Tenta gerar e inserir uma chave única
    for ($i = 0; $i < 12; $i++) {
      $chave = generate_game_key();

      $ins = $conn->prepare('INSERT INTO biblioteca (user_id, jogo_id, chave, compra_data) VALUES (?,?,?,?)');
      if (!$ins) {
        throw new RuntimeException('Prepare falhou: ' . $conn->error);
      }
      $ins->bind_param('iiss', $userId, $jogoId, $chave, $now);

      // Caso bem sucedido e sem duplicidade, retorna a chave
      if ($ins->execute()) {
        return $chave;
      }

      // Caso mal sucedido mas sem erro de duplicidade, gera uma exceção
      if ($conn->errno !== 1062) {
        throw new RuntimeException('Falha ao registrar chave: ' . $conn->error);
      }
    }

    // Caso erro de duplicidade, volta para o "for" e tenta gerar uma chave única até 12 vezes
    throw new RuntimeException('Não foi possível gerar uma chave única.');
  }
}