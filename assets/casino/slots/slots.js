// casino/slots/slots.js
$(document).ready(function() {
    const spinButton = $('#spinButton');
    const reels = [$('#reel1'), $('#reel2'), $('#reel3')];
    const winTitleDisplay = $('#winTitle');
    const winAmountDisplay = $('#winAmount');
    const vaultAmountDisplay = $('#vaultAmountDisplay');
    const vaultBar = $('#vaultBar');
    const userBalanceDisplay = $('#userBalanceDisplay');
    const messageArea = $('#messageArea');
    const betOptionsContainer = $('.bet-selector');
    const selectedBetDisplay = $('#selectedBetAmount');

    let currentBet = 0;
    let currentUserBalance = initialUserBalance;
    let currentVaultAmount = initialVaultAmount;
    let isSpinning = false;
    let spinInterval;
    let vaultUpdateInterval; // Untuk polling vault

    // --- Inisialisasi ---
    updateVaultDisplay(currentVaultAmount); // Set vault awal
    updateBalanceDisplay(currentUserBalance); // Set saldo awal
    updateBetButtonsState(currentUserBalance); // Enable/disable tombol bet awal

    // --- Fungsi Helper ---
    function formatCurrency(amount) {
        return '$' + amount.toLocaleString('en-US');
    }

    function showMessage(message, type = 'info') {
        messageArea.text(message).removeClass('success error').addClass(type);
        // Hapus pesan setelah beberapa detik
        setTimeout(() => {
            messageArea.text('').removeClass('success error info');
        }, 4000);
    }

    function updateBalanceDisplay(balance) {
        currentUserBalance = balance;
        userBalanceDisplay.text(formatCurrency(balance));
        updateBetButtonsState(balance); // Perbarui status tombol bet setiap saldo berubah
    }

    function updateVaultDisplay(amount, oldValue) {
        const displayElement = vaultAmountDisplay;
        const barElement = vaultBar;

        // GTA SA Animation
        if (oldValue !== undefined && amount !== oldValue) {
            const change = amount - oldValue;
            if (change > 0) {
                displayElement.addClass('increase');
            } else {
                displayElement.addClass('decrease');
            }
            // Hapus class setelah animasi selesai
            setTimeout(() => displayElement.removeClass('increase decrease'), 500);

            // Animasi angka (opsional, bisa kompleks)
            // Versi sederhana: langsung update
            displayElement.text(formatCurrency(amount));
        } else {
            displayElement.text(formatCurrency(amount)); // Update biasa saat awal
        }

        currentVaultAmount = amount; // Update variabel global

        // Update Vault Bar (persentase, bisa diatur skalanya)
        // Skala sederhana: anggap max $10M untuk 100% bar, bisa diubah
        const maxVaultForBar = 50000000; // Misal batas atas untuk visualisasi bar
        const percentage = Math.min(100, (amount / maxVaultForBar) * 100);
        barElement.css('width', percentage + '%');
    }


    function updateBetButtonsState(balance) {
        $('.bet-option').each(function() {
            const betAmount = parseInt($(this).data('amount'));
            if (balance < betAmount) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
        // Jika bet yang terpilih tidak lagi valid, reset
        if (currentBet > 0 && balance < currentBet) {
            deselectBet();
        }
    }

    function selectBet(amount) {
        currentBet = amount;
        selectedBetDisplay.text(formatCurrency(amount));
        spinButton.prop('disabled', false).text('Spin!'); // Aktifkan tombol spin
        // Tandai tombol yang dipilih
        $('.bet-option').removeClass('selected');
        $(`.bet-option[data-amount="${amount}"]`).addClass('selected');
    }

    function deselectBet() {
        currentBet = 0;
        selectedBetDisplay.text('Tidak ada');
        spinButton.prop('disabled', true).text('Pilih Bet');
        $('.bet-option').removeClass('selected');
    }

    function startSpinAnimation() {
        isSpinning = true;
        reels.forEach(reel => reel.addClass('spinning'));
        spinInterval = setInterval(() => {
            reels.forEach(reel => {
                const randomIndex = Math.floor(Math.random() * availableEmojis.length);
                reel.text(availableEmojis[randomIndex]);
            });
        }, 80); // Interval cepat untuk animasi
        winTitleDisplay.text('-');
        winAmountDisplay.text('$0');
        showMessage(''); // Hapus pesan lama
    }

    function stopSpinAnimation(finalReels, winTitle, winAmount) {
        clearInterval(spinInterval);
        reels.forEach((reel, index) => {
            reel.text(finalReels[index]).removeClass('spinning');
            // Efek 'bounce' kecil saat berhenti (opsional)
            reel.css('transform', 'scale(1.1)');
            setTimeout(() => reel.css('transform', 'scale(1)'), 150);
        });
        isSpinning = false;
        spinButton.prop('disabled', false); // Aktifkan lagi setelah selesai

        if (winAmount > 0) {
            winTitleDisplay.text(winTitle || 'Menang!');
            winAmountDisplay.text(formatCurrency(winAmount));
            showMessage(`Selamat! Anda memenangkan ${winTitle || 'sesuatu'}!`, 'success');
        } else {
            winTitleDisplay.text('-');
            winAmountDisplay.text('$0');
            showMessage('Coba lagi!', 'info');
        }
    }

    // --- Event Listeners ---
    betOptionsContainer.on('click', '.bet-option:not(:disabled)', function() {
        if (isSpinning) return; // Jangan ganti bet saat berputar
        const amount = parseInt($(this).data('amount'));
        selectBet(amount);
    });

    spinButton.on('click', function() {
        if (isSpinning || currentBet <= 0) return;

        if (currentUserBalance < currentBet) {
            showMessage('Uang Anda tidak cukup untuk bet ini!', 'error');
            return;
        }

        isSpinning = true;
        spinButton.prop('disabled', true).text('Berputar...'); // Disable tombol saat proses
        startSpinAnimation();

        // Kirim request ke server
        $.ajax({
            url: 'spin_logic.php', // Path ke file PHP backend
            type: 'POST',
            dataType: 'json', // Harapkan response JSON
            data: {
                bet_amount: currentBet
                // Kirim user_id jika diperlukan backend (biasanya dari session PHP saja)
            },
            success: function(response) {
                if (response.success) {
                    // Berhenti animasi dengan hasil dari server
                    stopSpinAnimation(
                        response.reels,
                        response.win_title,
                        response.win_amount
                    );
                    // Update saldo & vault dengan data dari server
                    updateBalanceDisplay(response.new_balance);
                    updateVaultDisplay(response.new_vault, currentVaultAmount); // Beri nilai lama untuk animasi
                    // Jackpot message
                    if (response.is_jackpot) {
                        showMessage(`🎉 JACKPOT! Anda memenangkan seluruh vault! 🎉`, 'success');
                    }

                } else {
                    // Ada error dari server (saldo tidak cukup, dll)
                    stopSpinAnimation(['😢', '😢', '😢'], 'Error', 0); // Tampilkan error visual
                    showMessage(response.message || 'Terjadi kesalahan.', 'error');
                    // Mungkin perlu fetch ulang state jika ada desync
                    fetchCurrentState();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Error koneksi atau server PHP error
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                stopSpinAnimation(['🔥', '🔥', '🔥'], 'Error', 0);
                showMessage('Error komunikasi dengan server.', 'error');
                isSpinning = false; // Pastikan bisa spin lagi
                spinButton.prop('disabled', false).text('Spin!');
            },
            complete: function() {
                // Beri jeda sedikit sebelum re-enable tombol spin jika diperlukan
                // setTimeout(() => {
                //     if (!isSpinning) spinButton.prop('disabled', currentBet <= 0);
                // }, 200);
                if (!isSpinning && currentBet > 0 && currentUserBalance >= currentBet) {
                    spinButton.prop('disabled', false).text('Spin!');
                } else if (!isSpinning) {
                    deselectBet(); // Reset jika tidak bisa spin lagi
                }
            }
        });
    });

    // --- Polling untuk Vault (Metode Sederhana untuk Live Update) ---
    function fetchCurrentState() {
        $.ajax({
            url: 'get_state.php', // Endpoint untuk get saldo & vault terbaru
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hanya update jika nilainya berubah untuk menghindari animasi berlebihan
                    if (response.current_balance !== currentUserBalance) {
                        updateBalanceDisplay(response.current_balance);
                    }
                    if (response.current_vault !== currentVaultAmount) {
                        updateVaultDisplay(response.current_vault, currentVaultAmount);
                    }
                } else {
                    console.warn("Gagal fetch state:", response.message);
                }
            },
            error: function() {
                // Tidak perlu tampilkan error ke user, coba lagi nanti
                console.warn("Gagal polling state.");
            }
        });
    }

    // Mulai polling setiap beberapa detik
    vaultUpdateInterval = setInterval(fetchCurrentState, 5000); // Cek setiap 5 detik

    // Hentikan polling jika user meninggalkan halaman (opsional)
    // $(window).on('beforeunload', function(){
    //     clearInterval(vaultUpdateInterval);
    // });

}); // End document ready
