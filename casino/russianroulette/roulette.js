// /casino/russianroulette/roulette.js
$(document).ready(function() {
    if (typeof GAME_DATA !== 'undefined') {
        const gameId = GAME_DATA.game_id;
        const myUserId = GAME_DATA.my_user_id;
        const shootButton = $('#shootButton');
        const gameMessage = $('#gameMessage');
        const chamberDisplay = $('#chamberDisplay');
        const playerCardCreator = $('#playerCard_' + GAME_DATA.creator_id);
        const pfpCreator = $('#pfp_' + GAME_DATA.creator_id);

        let currentTurnUserId = null;
        let currentStatus = 'loading';
        let isMyTurn = false;
        let pollInterval = null;
        let absoluteOpponentId = null;
        let isGameOver = false; // Flag penting
        let gameOverTimeout = null; // Untuk menyimpan timeout redirect

        const POLLING_INTERVAL_WAITING = 3000;
        const POLLING_INTERVAL_MY_TURN = 7000;
        const POLLING_INTERVAL_INITIAL_WAIT = 5000;
        const REDIRECT_DELAY = 5000; // Delay sebelum redirect (ms)

        // Fungsi untuk menampilkan pesan
        function showGameMessage(message, type = 'info', isLoading = false, forceShow = false) {
            // --- PERBAIKAN: Hanya blok jika game over DAN bukan forceShow ---
            if (isGameOver && !forceShow) return;
            gameMessage.removeClass('success error info loading warning').addClass(type); // Tambah warning
            let content = message;
            if (isLoading) {
                content += ' <i class="fas fa-spinner spinner"></i>';
                gameMessage.addClass('loading');
            }
            gameMessage.html(content);
        }

        // Fungsi untuk mengatur interval polling (Tidak berubah)
        function managePolling(newStatus, nextTurnUserId) {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            if (newStatus !== 'active' && newStatus !== 'waiting') {
                 // Cek apakah memang sudah game over sebelumnya
                 if (!isGameOver) {
                    isGameOver = (newStatus === 'finished' || newStatus === 'cancelled' || newStatus === 'not_found');
                 }
                return;
            }

            let intervalTime = POLLING_INTERVAL_WAITING;
            if (newStatus === 'active') {
                if (nextTurnUserId === myUserId) {
                    intervalTime = POLLING_INTERVAL_MY_TURN;
                }
            } else if (newStatus === 'waiting') {
                 intervalTime = POLLING_INTERVAL_INITIAL_WAIT;
            }
            pollInterval = setInterval(fetchGameState, intervalTime);
        }

        // Fungsi untuk update UI (Tidak berubah signifikan)
        function updateGameStateUI(state) {
            if (!state || isGameOver) return;

            const previousStatus = currentStatus;
            const previousTurnUserId = currentTurnUserId; // Simpan giliran sebelumnya
            currentStatus = state.status;
            currentTurnUserId = state.current_turn_user_id;
            isMyTurn = (currentTurnUserId === myUserId && currentStatus === 'active');
            absoluteOpponentId = state.opponent_id;

            const playerCardOpponent = absoluteOpponentId ? $('#playerCard_' + absoluteOpponentId) : null;
            const pfpOpponent = absoluteOpponentId ? $('#pfp_' + absoluteOpponentId) : null;

            // Update PFP & Nama (logic sama)
            const creatorPfpSrc = state.creator_pfp || GAME_DATA.player1_pfp || DEFAULT_PFP;
            const opponentPfpSrc = state.opponent_pfp || GAME_DATA.player2_pfp || DEFAULT_PFP;
            if (pfpCreator.length && pfpCreator.attr('src') !== creatorPfpSrc) pfpCreator.attr('src', creatorPfpSrc);
             if (playerCardOpponent && playerCardOpponent.length) { // Update lawan jika ada
                 if (pfpOpponent && pfpOpponent.length && pfpOpponent.attr('src') !== opponentPfpSrc) pfpOpponent.attr('src', opponentPfpSrc);
                 const opponentNameElement = playerCardOpponent.find('h3');
                 const currentOpponentName = state.opponent_username || 'Lawan';
                 if (opponentNameElement.text() !== currentOpponentName) opponentNameElement.text(currentOpponentName);
             } else if (!absoluteOpponentId && playerCardCreator.siblings().length > 1) { // Handle waiting state
                 const waitingCard = playerCardCreator.siblings('.rr-player-card');
                 if (waitingCard.length){
                     const waitingPfp = waitingCard.find('.profile-pic');
                     const waitingName = waitingCard.find('h3');
                     if(waitingPfp.attr('src') !== DEFAULT_PFP) waitingPfp.attr('src', DEFAULT_PFP);
                     if(waitingName.text() !== 'Menunggu...') waitingName.text('Menunggu...');
                 }
             }


            // Update highlight & status teks pemain
            playerCardCreator.removeClass('active-turn');
             if(playerCardOpponent && playerCardOpponent.length) playerCardOpponent.removeClass('active-turn');
             playerCardCreator.find('.player-status').text('');
             if(playerCardOpponent && playerCardOpponent.length) playerCardOpponent.find('.player-status').text('');

            if (currentStatus === 'active') {
                 const activePlayerCard = (currentTurnUserId === GAME_DATA.creator_id) ? playerCardCreator : playerCardOpponent;
                 if (activePlayerCard && activePlayerCard.length) {
                     activePlayerCard.addClass('active-turn');
                     activePlayerCard.find('.player-status').text('Giliran Dia');
                 }
            }

            // Update status chamber (logic sama)
            const fired = state.chambers_fired ? state.chambers_fired.split(',') : [];
            chamberDisplay.find('.rr-chamber').each(function() {
                const chamberNum = $(this).data('chamber').toString();
                 $(this).removeClass('fired-blank fired-live current');
                 if (fired.includes(chamberNum)) {
                    $(this).addClass('fired-blank');
                 }
            });
             if (isMyTurn) {
                 let nextChamber = 0;
                 for (let i = 1; i <= state.max_chambers; i++) {
                     if (!fired.includes(i.toString())) {
                         nextChamber = i;
                         break;
                     }
                 }
                 if (nextChamber > 0) {
                    chamberDisplay.find(`.rr-chamber[data-chamber="${nextChamber}"]`).addClass('current');
                 }
             }

            // Update tombol shoot SELALU di akhir update UI
            shootButton.prop('disabled', !isMyTurn);

            // Update Pesan Utama (jika game masih berjalan)
            if (currentStatus === 'waiting') {
                 showGameMessage('Menunggu lawan bergabung...', 'info', true);
            } else if (currentStatus === 'active') {
                if (isMyTurn) {
                    if (!gameMessage.text().startsWith('Giliran Anda')) {
                        showGameMessage('Giliran Anda! Tekan tombol Tembak.', 'info');
                    }
                } else {
                    const opponentUsername = state.opponent_username || state.creator_username;
                    showGameMessage(`Menunggu giliran ${opponentUsername}...`, 'info', true);
                }
            }

            // Atur ulang polling jika status atau giliran berubah
            if (state.status !== previousStatus || state.current_turn_user_id !== previousTurnUserId) {
                 managePolling(currentStatus, currentTurnUserId);
            }
        }

         // Fungsi khusus untuk menangani akhir permainan
         function handleGameOver(state) {
             if (isGameOver) return; // Jangan jalankan lagi jika sudah over
             isGameOver = true;
              console.log("Handling Game Over:", state); // Debugging
             if (pollInterval) clearInterval(pollInterval);
             pollInterval = null;
             if (gameOverTimeout) clearTimeout(gameOverTimeout); // Hapus timeout lama jika ada

             shootButton.prop('disabled', true);
             playerCardCreator.removeClass('active-turn');
             if(absoluteOpponentId) $('#playerCard_' + absoluteOpponentId)?.removeClass('active-turn'); // Gunakan ID absolut

             let message = "";
             let messageType = "info";
             let loserId = null; // Untuk menandai PFP yang kalah

             if (state.status === 'finished') {
                 const winner = state.winner_username || '???';
                 const pot = state.pot || 0;
                 message = `Permainan Selesai! <strong>${winner}</strong> memenangkan $${pot.toLocaleString()}.`;
                 messageType = 'success';

                 // Cari loser ID
                 if (state.winner_id && GAME_DATA.creator_id && absoluteOpponentId) {
                    loserId = (state.winner_id === GAME_DATA.creator_id) ? absoluteOpponentId : GAME_DATA.creator_id;
                 }

                 // Tampilkan status menang/kalah pada kartu
                 const winnerCard = (state.winner_id === GAME_DATA.creator_id) ? playerCardCreator : (absoluteOpponentId ? $('#playerCard_' + absoluteOpponentId) : null);
                 const loserCard = loserId ? $('#playerCard_' + loserId) : null;

                 if (winnerCard && winnerCard.length) winnerCard.find('.player-status').text('Menang!');
                 if (loserCard && loserCard.length) loserCard.find('.player-status').text('Kalah!');

                  // Tandai chamber yang kena jika informasinya ada
                  // Perlu modifikasi backend take_turn & get_state untuk mengirim chamber_fired_live saat game over
                  const firedChamberElement = chamberDisplay.find(`.rr-chamber[data-chamber="${state.chamber_fired_live}"]`);
                  if(firedChamberElement && firedChamberElement.length) {
                      firedChamberElement.removeClass('fired-blank').addClass('fired-live');
                  }

             } else if (state.status === 'cancelled') {
                 message = 'Permainan ini telah dibatalkan.';
                 messageType = 'warning';
             } else if (state.status === 'not_found') {
                  message = 'Error: Permainan tidak ditemukan.';
                  messageType = 'error';
             }

             // --- PERBAIKAN: Gunakan forceShow agar pesan akhir tampil ---
             showGameMessage(message, messageType, false, true);

             // Jadwalkan redirect
              gameOverTimeout = setTimeout(() => {
                  console.log("Redirecting to lobby..."); // Debugging
                 window.location.href = 'index.php?status=Kembali ke Lobi';
             }, REDIRECT_DELAY);
         }


        // Fungsi untuk mengambil state game terbaru dari server
        function fetchGameState() {
             if (isGameOver) {
                 if (pollInterval) clearInterval(pollInterval);
                 return;
             }
            // console.log("Fetching game state..."); // Debugging

            $.ajax({
                url: 'get_game_state.php',
                type: 'GET',
                dataType: 'json',
                data: { id: gameId },
                timeout: 8000,
                success: function(response) {
                    if (isGameOver) return;
                    // console.log("Fetch success:", response); // Debugging

                    if (response.success && response.game_state) {
                         // --- PERBAIKAN: Cek status di sini untuk panggil handleGameOver ---
                         if (response.game_state.status === 'finished' || response.game_state.status === 'cancelled' || response.game_state.status === 'not_found') {
                             handleGameOver(response.game_state);
                         } else {
                             updateGameStateUI(response.game_state);
                         }
                    } else {
                        console.warn("Gagal fetch state:", response.message);
                         if (response.game_state && response.game_state.status === 'not_found') {
                              handleGameOver(response.game_state);
                         }
                    }
                },
                error: function(jqXHR, textStatus) {
                    if (textStatus !== 'abort' && !isGameOver) {
                        console.warn("Network error fetching game state:", textStatus);
                    }
                }
            });
        }

        // Fungsi untuk melakukan aksi tembak
        function takeTurn() {
            if (!isMyTurn || isGameOver) return;

            shootButton.prop('disabled', true);
            showGameMessage('Menembak...', 'info', true);
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = null;
             // Hapus timeout redirect lama jika ada (kasus klik tembak cepat setelah menang/kalah?)
             if(gameOverTimeout) clearTimeout(gameOverTimeout);

            $.ajax({
                url: 'take_turn.php',
                type: 'POST',
                dataType: 'json',
                data: { game_id: gameId },
                success: function(response) {
                    console.log("Take turn response:", response); // Debugging
                    if (response.success) {
                        const firedChamberElement = chamberDisplay.find(`.rr-chamber[data-chamber="${response.chamber_fired}"]`);
                        firedChamberElement.removeClass('current');

                        if (response.game_over) {
                             // --- PERBAIKAN: Langsung panggil handleGameOver ---
                             handleGameOver({ // Buat objek state minimal untuk game over
                                 status: 'finished',
                                 winner_id: response.winner_id,
                                 winner_username: (response.winner_id === GAME_DATA.creator_id) ? GAME_DATA.player1_username : GAME_DATA.player2_username,
                                 pot: response.payout,
                                 chamber_fired_live: response.chamber_fired, // Kirim chamber yg live
                                 creator_id: GAME_DATA.creator_id, // Sertakan ID pemain
                                 opponent_id: absoluteOpponentId
                             });
                             firedChamberElement.addClass('fired-live'); // Update UI chamber live
                             showGameMessage(response.message, 'error', false, true); // Tampilkan pesan kalah (paksa)

                        } else {
                             firedChamberElement.addClass('fired-blank');
                             showGameMessage(response.message, 'success'); // Tampilkan pesan blank
                             isMyTurn = false; // Update status lokal
                             currentTurnUserId = response.next_turn_user_id; // Simpan giliran berikutnya
                             // Tidak perlu update UI parsial, fetchGameState akan dipanggil
                             fetchGameState(); // Panggil fetch state SEGERA setelah giliran selesai
                             managePolling('active', currentTurnUserId); // Atur polling untuk menunggu lawan
                        }
                    } else {
                        showGameMessage(response.message || 'Terjadi kesalahan saat menembak.', 'error');
                        shootButton.prop('disabled', !isMyTurn); // Re-enable jika masih gilirannya
                        managePolling(currentStatus, currentTurnUserId); // Mulai polling lagi jika error
                    }
                },
                error: function() {
                    showGameMessage('Error jaringan saat mencoba menembak.', 'error');
                    shootButton.prop('disabled', !isMyTurn);
                    managePolling(currentStatus, currentTurnUserId);
                }
            });
        }

        // --- Inisialisasi ---
        shootButton.on('click', takeTurn);

        // Ambil state awal saat halaman load
        showGameMessage('Memuat status permainan...', 'info', true);
        fetchGameState(); // Panggil fetch pertama kali untuk memulai loop

        // Hentikan polling & timeout jika user meninggalkan halaman
         $(window).on('beforeunload', function() {
             if (pollInterval) clearInterval(pollInterval);
             if (gameOverTimeout) clearTimeout(gameOverTimeout);
         });

    } // End of check if GAME_DATA exists
});