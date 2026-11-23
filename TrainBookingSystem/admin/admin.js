
//coach&seat

let coaches = [];
let activeCoach = "Coach C";
let selectedSeat = null;
let seats = {};

const seatLabels = [];

for (let row = 1; row <= 12; row++) {
    ["A", "B", "C", "D"].forEach(letter => {
        seatLabels.push(row + letter);
    });
}

const statusTypes = [
    "available",
    "occupied",
    "reserved",
    "unavailable"
];

//seat from database
async function loadSeats() {

    try {
        const response = await fetch("seat_api.php");
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        seats = {};
        coaches = [];

        data.seats.forEach(seat => {

            if (!seats[seat.coach]) {
                seats[seat.coach] = [];

                coaches.push({
                    name: seat.coach,
                    class: seat.seat_class,
                    rows: 12,
                    seats: 48
                });
            }

            seats[seat.coach].push({
                id: Number(seat.id),
                label: seat.seat_number,
                status: seat.seat_status.toLowerCase()
            });

        });

        if (!seats[activeCoach] && coaches.length > 0) {
            activeCoach = coaches[0].name;
        }

        renderCoachTabs();
        renderSeatMap();
        renderCoachTable();
        updateSummary();

    } catch (error) {
        console.error("Database error:", error);
        alert("Unable to load seats from database. Please check seat_api.php.");
    }
}

//displaying seat
function renderSeatMap() {

    const seatMap = document.getElementById("seatMap");

    if (!seatMap) return;

    seatMap.innerHTML = "";

    const coachFront = document.createElement("div");
    coachFront.className = "coach-front";
    coachFront.textContent = `← Rear | ${activeCoach} | Front →`;

    seatMap.appendChild(coachFront);

    const coachSeats = seats[activeCoach];

    if (!coachSeats) return;

    for (let rowIndex = 0; rowIndex < 12; rowIndex++) {

        const row = document.createElement("div");
        row.className = "seat-row";

        const rowSeats = coachSeats.slice(
            rowIndex * 4,
            rowIndex * 4 + 4
        );

        rowSeats.forEach((seat, seatIndex) => {

            if (seatIndex === 2) {
                const aisle = document.createElement("div");
                aisle.className = "aisle";
                row.appendChild(aisle);
            }

            const button = document.createElement("button");

            button.className = "seat " + seat.status;
            button.textContent = seat.label;
            button.title = seat.label + " - " + seat.status;

            if (selectedSeat === seat.label) {
                button.classList.add("selected");
            }

            button.addEventListener("click", function () {
                selectSeat(seat.label);
            });

            row.appendChild(button);

        });

        seatMap.appendChild(row);
    }

}
//selecting seat
function selectSeat(label) {

    selectedSeat = label;

    const seat = seats[activeCoach]?.find(
        s => s.label === label
    );

    if (!seat) return;

    const seatInfo = document.getElementById("seatInfo");

    seatInfo.className = "seat-info";

    seatInfo.innerHTML = `
        <strong>${activeCoach} - Seat ${seat.label}</strong><br>
        Class: ${getCoachClass(activeCoach)}<br>
        Seat Status: ${seat.status.toUpperCase()}
    `;

    const actions = document.getElementById("seatActions");

    actions.innerHTML = "";

    statusTypes.forEach(status => {

        const button = document.createElement("button");

        button.textContent =
            status.charAt(0).toUpperCase() + status.slice(1);

        button.disabled = seat.status === status;

        button.addEventListener("click", async function () {

            await updateSeatStatus(seat.id, status, label);

        });

        actions.appendChild(button);

    });

    renderSeatMap();
}
//updating seat
async function updateSeatStatus(id, status, label) {

    try {

        const formData = new URLSearchParams();

        formData.append("action", "update_status");
        formData.append("id", id);
        formData.append("status", 
            status.charAt(0).toUpperCase() + status.slice(1)
        );

        const response = await fetch("seat_api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        // Reload updated database information
        await loadSeats();

        // Keep the selected coach and seat
        activeCoach = activeCoach;
        selectedSeat = label;

        renderSeatMap();
        selectSeat(label);

        alert("Seat status updated successfully!");

    } catch (error) {
        console.error(error);
        alert("Failed to update seat status: " + error.message);
    }

}
//coach class

function getCoachClass(coachName) {

    const coach = coaches.find(c => c.name === coachName);

    return coach ? coach.class : "Gold";

}
//summary cards
function updateSummary() {

    const allSeats = Object.values(seats).flat();

    let available = 0;
    let occupied = 0;
    let reserved = 0;
    let unavailable = 0;

    allSeats.forEach(seat => {

        if (seat.status === "available") available++;
        if (seat.status === "occupied") occupied++;
        if (seat.status === "reserved") reserved++;
        if (seat.status === "unavailable") unavailable++;

    });

    document.getElementById("total").textContent = allSeats.length;
    document.getElementById("available").textContent = available;
    document.getElementById("occupied").textContent = occupied;
    document.getElementById("reserved").textContent = reserved;
    document.getElementById("unavailable").textContent = unavailable;

}
//coach table

function renderCoachTable() {

    const tbody = document.getElementById("coachTable");

    if (!tbody) return;

    tbody.innerHTML = "";

    coaches.forEach(coach => {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${coach.name}</td>
            <td>${coach.class}</td>
            <td>${coach.rows}</td>
            <td>${coach.seats}</td>
            <td>
                <button class="edit-btn">Edit</button>
            </td>
        `;

        row.querySelector(".edit-btn").addEventListener("click", function () {
            editCoach(coach);
        });

        tbody.appendChild(row);

    });

}
//editting coach

async function editCoach(coach) {

    const oldName = coach.name;

    const newName = prompt("Enter new coach name:", oldName);

    if (!newName || newName.trim() === "") return;

    const cleanName = newName.trim();

    const newClass = prompt("Enter coach class (Gold or Silver):", coach.class);

    if (!newClass || newClass.trim() === "") return;

    const cleanClass = newClass.trim();

    if (!["Gold", "Silver"].includes(cleanClass)) {
        alert("Please enter Gold or Silver only!");
        return;
    }

    try {

        const formData = new URLSearchParams();

        formData.append("action", "edit_coach");
        formData.append("old_name", oldName);
        formData.append("new_name", cleanName);
        formData.append("class", cleanClass);

        const response = await fetch("seat_api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        activeCoach = cleanName;
        selectedSeat = null;

        await loadSeats();

        alert("Coach updated successfully!");

    } catch (error) {

        alert("Unable to edit coach: " + error.message);

    }

}
//displaying coach

function renderCoachTabs() {

    const tabs = document.getElementById("coachTabs");

    if (!tabs) return;

    tabs.innerHTML = "";

    coaches.forEach(coach => {

        const button = document.createElement("button");

        button.className = "tab";

        if (coach.name === activeCoach) {
            button.classList.add("active");
        }

        button.textContent = coach.name + " · " + coach.class;
        button.dataset.coach = coach.name;

        button.addEventListener("click", function () {

            activeCoach = coach.name;
            selectedSeat = null;

            document.getElementById("seatInfo").className = "seat-info empty";
            document.getElementById("seatInfo").textContent = "No seat selected";
            document.getElementById("seatActions").innerHTML = "";

            renderCoachTabs();
            renderSeatMap();

        });

        tabs.appendChild(button);

    });

}

//adding coach

async function addCoach() {

    const name = prompt("Enter new coach name (Example: Coach H):");

    if (!name || name.trim() === "") return;

    const cleanName = name.trim();

    const coachClass = prompt("Enter coach class (Gold or Silver):", "Gold");

    if (!coachClass || coachClass.trim() === "") return;

    const cleanClass = coachClass.trim();

    if (!["Gold", "Silver"].includes(cleanClass)) {
        alert("Please enter Gold or Silver only!");
        return;
    }

    try {

        const formData = new URLSearchParams();

        formData.append("action", "add_coach");
        formData.append("name", cleanName);
        formData.append("class", cleanClass);

        const response = await fetch("seat_api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message);
        }

        activeCoach = cleanName;
        selectedSeat = null;

        await loadSeats();

        alert("New coach added successfully with 48 seats!");

    } catch (error) {

        alert("Unable to add coach: " + error.message);

    }

}
//searching coach
document.getElementById("seatSearch").addEventListener("input", function () {

    const value = this.value.trim().toUpperCase();

    if (seatLabels.includes(value)) {
        selectSeat(value);
    }

});

//filtering coach

document.getElementById("coachSelect").addEventListener("change", function () {

    const coachName = this.value;

    if (coachName !== "All" && seats[coachName]) {

        activeCoach = coachName;
        selectedSeat = null;

        document.getElementById("seatInfo").className = "seat-info empty";
        document.getElementById("seatInfo").textContent = "No seat selected";
        document.getElementById("seatActions").innerHTML = "";

        renderCoachTabs();
        renderSeatMap();

    }

});
//filtering class

document.getElementById("classSelect").addEventListener("change", function () {

    const selectedClass = this.value;

    document.querySelectorAll(".tab").forEach(tab => {

        const coachName = tab.dataset.coach;
        const coach = coaches.find(c => c.name === coachName);

        if (!coach) return;

        tab.style.display =
            selectedClass === "All" || coach.class === selectedClass
                ? "inline-block"
                : "none";

    });

});
//filtering status

document.getElementById("statusSelect").addEventListener("change", function () {

    const selectedStatus = this.value.toLowerCase();

    document.querySelectorAll(".seat").forEach(button => {

        button.style.opacity =
            selectedStatus === "all" ||
            button.classList.contains(selectedStatus)
                ? "1"
                : "0.2";

    });

});
//resting the filters

function resetFilters() {

    document.getElementById("classSelect").value = "All";
    document.getElementById("coachSelect").value = "All";
    document.getElementById("statusSelect").value = "All";
    document.getElementById("seatSearch").value = "";

    document.getElementById("fromSelect").selectedIndex = 0;
    document.getElementById("toSelect").selectedIndex = 0;

    document.querySelectorAll(".tab").forEach(tab => {
        tab.style.display = "inline-block";
    });

    document.querySelectorAll(".seat").forEach(button => {
        button.style.opacity = "1";
    });

    activeCoach = coaches.length > 0 ? coaches[0].name : "Coach C";
    selectedSeat = null;

    document.getElementById("seatInfo").className = "seat-info empty";
    document.getElementById("seatInfo").textContent = "No seat selected";
    document.getElementById("seatActions").innerHTML = "";

    renderCoachTabs();
    renderSeatMap();

}

//starting the page
loadSeats();
