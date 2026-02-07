document.addEventListener("DOMContentLoaded", function () {
  fetch('mottavannupadeyiri_zehn.php')
    .then(response => response.json())
    .then(data => {
      const numberColors = {
        0: 'linear-gradient(to right, #a044ff, #6a3093)',
        1: 'linear-gradient(to right, #56ab2f, #a8e063)',
        2: 'linear-gradient(to right, #e52d27, #b31217)',
        3: 'linear-gradient(to right, #56ab2f, #a8e063)',
        4: 'linear-gradient(to right, #e52d27, #b31217)',
        5: 'linear-gradient(to right, #56ab2f, #a8e063)',
        6: 'linear-gradient(to right, #e52d27, #b31217)',
        7: 'linear-gradient(to right, #56ab2f, #a8e063)',
        8: 'linear-gradient(to right, #e52d27, #b31217)',
        9: 'linear-gradient(to right, #56ab2f, #a8e063)'
      };

      const container = document.getElementById('bet-stats-cards');
      container.innerHTML = '';

      for (let i = 0; i <= 9; i++) {
        const card = `
          <div class="col-md-2 col-sm-4 col-6 mb-3">
            <div class="card text-center shadow-sm p-2">
              <div class="card-body p-2">
                <p class="mb-1 fw-semibold">Number: ${i}</p>
                <div class="btn btn-sm w-100 text-white fw-bold" style="background: ${numberColors[i]};">
                  Total Bet: ৳${data.numbers[i] ?? 0}
                </div>
              </div>
            </div>
          </div>
        `;
        container.innerHTML += card;
      }

      // Total Users
      container.innerHTML += `
        <div class="col-md-2 col-sm-4 col-6 mb-3">
          <div class="card text-center shadow-sm p-2">
            <div class="card-body p-2">
              <p class="mb-1 fw-semibold">Total Users Bet</p>
              <div class="btn btn-sm w-100 text-white fw-bold" style="background: linear-gradient(to right, #36d1dc, #5b86e5);">
                ${data.total_users} Users
              </div>
            </div>
          </div>
        </div>`;

      // Total Amount
      container.innerHTML += `
        <div class="col-md-2 col-sm-4 col-6 mb-3">
          <div class="card text-center shadow-sm p-2">
            <div class="card-body p-2">
              <p class="mb-1 fw-semibold">Total Amount Bet</p>
              <div class="btn btn-sm w-100 text-white fw-bold" style="background: linear-gradient(to right, #ff512f, #dd2476);">
                ৳${data.total_amount}
              </div>
            </div>
          </div>
        </div>`;
    })
    .catch(error => {
      console.error("Failed to fetch betting data:", error);
    });
});
