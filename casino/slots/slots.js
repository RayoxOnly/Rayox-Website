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
    let isOnCooldown = false; // Flag untuk cooldown
    const COOLDOWN_TIME = 2000; // Cooldown 2 detik (dalam milidetik)

// Definisikan Emojis (HANYA EMOJI DARI PAYTABLE untuk visual spin)
const visualSpinEmojis = ['🦇', '🦆', '🍫', '🔫', '🧑', '🚗', '☣️', '7️⃣'];


// --- Inisialisasi ---
updateVaultDisplay(currentVaultAmount); // Set vault awal
updateBalanceDisplay(currentUserBalance); // Set saldo awal
updateBetButtonsState(currentUserBalance); // Enable/disable tombol bet awal

// --- Fungsi Helper ---
function formatCurrency(amount) {
    const numAmount = Number(amount);
    if (isNaN(numAmount)) return '$?';
    return '$' + numAmount.toLocaleString('en-US');
}

function showMessage(message, type = 'info') {
    messageArea.text(message).removeClass('success error info').addClass(type);
    setTimeout(() => {
        if (messageArea.text() === message) {
            messageArea.text('').removeClass('success error info');
        }
    }, 5000);
}

function updateBalanceDisplay(balance) {
    currentUserBalance = Number(balance);
    userBalanceDisplay.text(formatCurrency(currentUserBalance));
    updateBetButtonsState(currentUserBalance);
}

function updateVaultDisplay(amount, oldValue) {
    const displayElement = vaultAmountDisplay;
    const barElement = vaultBar;
    const numericAmount = Number(amount);

    if (isNaN(numericAmount)) {
        console.error("Invalid vault amount received:", amount);
        displayElement.text('$?');
        barElement.css('width', '0%');
        return;
    }

    const numericOldValue = Number(oldValue);
    if (oldValue !== undefined && !isNaN(numericOldValue) && numericAmount !== numericOldValue) {
        const change = numericAmount - numericOldValue;
        displayElement.addClass(change > 0 ? 'increase' : 'decrease');
        setTimeout(() => displayElement.removeClass('increase decrease'), 500);

        let currentDisplay = numericOldValue;
        const step = (numericAmount - numericOldValue) / 10;
        let interval = setInterval(() => {
            currentDisplay += step;
            if ((step >= 0 && currentDisplay >= numericAmount) || (step < 0 && currentDisplay <= numericAmount)) {
                clearInterval(interval);
                currentDisplay = numericAmount;
            }
            displayElement.text(formatCurrency(Math.round(currentDisplay)));
        }, 30);

    } else {
        displayElement.text(formatCurrency(numericAmount));
    }

    currentVaultAmount = numericAmount;

    const maxVaultForBar = 50000000; // Sesuaikan jika perlu
    const percentage = Math.min(100, Math.max(0,(numericAmount / maxVaultForBar) * 100));
    barElement.css('width', percentage + '%');
}


function updateBetButtonsState(balance) {
    const numericBalance = Number(balance);
    $('.bet-option').each(function() {
        const betAmount = parseInt($(this).data('amount'));
        $(this).prop('disabled', isNaN(numericBalance) || numericBalance < betAmount);
    });
    if (currentBet > 0 && (isNaN(numericBalance) || numericBalance < currentBet)) {
        deselectBet();
    }
}

function selectBet(amount) {
    currentBet = parseInt(amount);
    selectedBetDisplay.text(formatCurrency(currentBet));
    // Logika untuk tombol Spin: disable jika cooldown, atau saldo kurang, atau bet 0
    if (isOnCooldown) {
        spinButton.prop('disabled', true).text('Tunggu...');
    } else if (currentUserBalance < currentBet || currentBet <= 0) {
        spinButton.prop('disabled', true).text(currentBet <= 0 ? 'Pilih Bet' : 'Saldo Kurang');
    } else {
        spinButton.prop('disabled', false).text('Spin!');
    }
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
            const randomIndex = Math.floor(Math.random() * visualSpinEmojis.length);
            reel.text(visualSpinEmojis[randomIndex]);
        });
    }, 80);
    winTitleDisplay.text('-');
    winAmountDisplay.text('$0');
    showMessage('');
}

function stopSpinAnimation(finalReels = ['?','?','?'], winTitle = '-', winAmount = 0) {
    clearInterval(spinInterval);
    if (!Array.isArray(finalReels) || finalReels.length !== 3) {
        finalReels = ['🔥', '🔥', '🔥'];
    }
    reels.forEach((reel, index) => {
        reel.text(finalReels[index]).removeClass('spinning');
        reel.css('transform', 'scale(1.1)');
        setTimeout(() => reel.css('transform', 'scale(1)'), 150);
    });

    winTitleDisplay.text(winTitle === 'Error' ? 'Error' : (winAmount > 0 ? (winTitle || 'Menang!') : '-'));
    winAmountDisplay.text(formatCurrency(winAmount));
}

// --- Event Listeners ---
betOptionsContainer.on('click', '.bet-option:not(:disabled)', function() {
    // === PERUBAHAN DI SINI ===
    // Hapus cek isOnCooldown, hanya cek isSpinning
    if (isSpinning) return; // Jangan ganti bet HANYA saat reel sedang berputar
    // === AKHIR PERUBAHAN ===

    const amount = parseInt($(this).data('amount'));
    selectBet(amount); // Panggil fungsi selectBet yang akan mengatur status tombol Spin
});

spinButton.on('click', function() {
    // Cek Cooldown HANYA saat akan spin
    if (isSpinning || currentBet <= 0 || isOnCooldown) {
        if(isOnCooldown) showMessage("Harap tunggu sebentar...", "info");
        return;
    }
    if (currentUserBalance < currentBet) {
        showMessage('Uang Anda tidak cukup untuk bet ini!', 'error');
        return;
    }

    // Mulai Cooldown & Proses Spin
    isSpinning = true;
    isOnCooldown = true; // Aktifkan cooldown HANYA saat spin dimulai
    spinButton.prop('disabled', true).text('Berputar...');
    startSpinAnimation();

    // Mulai Timer Cooldown
    const cooldownTimeout = setTimeout(() => {
        isOnCooldown = false; // Matikan flag cooldown
        // Coba re-enable tombol spin jika kondisi memungkinkan SEKARANG
        if (!isSpinning && currentBet > 0 && currentUserBalance >= currentBet) {
            spinButton.prop('disabled', false).text('Spin!');
        } else if (!isSpinning) {
            spinButton.prop('disabled', true).text(currentBet <= 0 ? 'Pilih Bet' : 'Saldo Kurang');
        }
        // Jika masih spinning, tombol akan di-handle oleh AJAX complete
    }, COOLDOWN_TIME);


    // Kirim request ke server (AJAX Call)
    $.ajax({
        url: 'spin_logic.php',
        type: 'POST',
        dataType: 'json',
        data: { bet_amount: currentBet },
        success: function(response) {
            if (response.success) {
                stopSpinAnimation(response.reels, response.win_title, response.win_amount);
                updateBalanceDisplay(response.new_balance);
                updateVaultDisplay(response.new_vault, currentVaultAmount);

                if (response.is_jackpot) {
                    showMessage(`🎉 JACKPOT! Anda memenangkan seluruh vault! 🎉`, 'success');
                } else if (response.win_amount > 0) {
                    showMessage(`Anda memenangkan ${response.win_title || 'sesuatu'}! (${formatCurrency(response.win_amount)})`, 'success');
                } else {
                    showMessage('Belum beruntung, coba lagi!', 'info');
                }

                if (response.new_balance < currentBet && currentBet > 0) {
                    deselectBet();
                    showMessage('Saldo tidak cukup untuk bet yang sama.', 'info');
                }
            } else {
                stopSpinAnimation(['😢', '😢', '😢'], 'Error', 0);
                showMessage(response.message || 'Terjadi kesalahan server.', 'error');
                fetchCurrentState();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
            stopSpinAnimation(['🔥', '🔥', '🔥'], 'Error', 0);
            showMessage('Error komunikasi dengan server.', 'error');
            fetchCurrentState();
        },
        complete: function(jqXHR, textStatus) {
            isSpinning = false;
            const responseData = (textStatus === 'success' && jqXHR.responseJSON) ? jqXHR.responseJSON : null;
            const finalBalance = (responseData && responseData.success) ? responseData.new_balance : currentUserBalance;

            // Atur status akhir tombol berdasarkan cooldown dan kondisi lain
            if (isOnCooldown) {
                spinButton.prop('disabled', true).text('Tunggu...'); // Masih cooldown
            } else if (currentBet <= 0 || finalBalance < currentBet) {
                spinButton.prop('disabled', true).text(currentBet <= 0 ? 'Pilih Bet' : 'Saldo Kurang');
            } else {
                spinButton.prop('disabled', false).text('Spin!'); // Cooldown selesai, bisa spin
            }
        }
    });
});

// --- Polling untuk Vault & Balance ---
function fetchCurrentState() {
    if (isSpinning) return;
    $.ajax({
        url: 'get_state.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (Number(response.current_balance) !== currentUserBalance) {
                    updateBalanceDisplay(response.current_balance);
                }
                if (Number(response.current_vault) !== currentVaultAmount) {
                    updateVaultDisplay(response.current_vault, currentVaultAmount);
                }
            } else {
                console.warn("Polling Gagal:", response.message);
            }
        },
        error: function() { /* console.warn("Polling state network error."); */ },
           timeout: 4000
    });
}

if (vaultUpdateInterval) clearInterval(vaultUpdateInterval);
vaultUpdateInterval = setInterval(fetchCurrentState, 5000);

}); // End document ready
