<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RailMY - Manage Seat Inventory</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="app">
    //sidebar
    <aside class="sidebar">
        <h2>🚆 RailMY</h2>

        <div class="nav-label">MANAGEMENT</div>
        <a href="#" class="nav-item"> Manage Booking</a>
        <a href="#" class="nav-item active"> Manage Seat Inventory</a>

        <div class="nav-label">ACCOUNT</div>
        <a href="#" class="nav-item"> Log out</a>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Manage Seat Inventory</h1>
                <p>Manage coach layouts and seat availability by route.</p>
            </div>
            <button class="primary" onclick="addCoach()">+ Add / manage coach</button>
        </div>

        //filtering 
        <section class="filters panel">

            <div class="field">
                <label>TRAIN</label>
                <select id="trainSelect">
                    <option>ETS 23 - KL Sentral → Butterworth</option>
                    <option>ETS 21 - KL Sentral → Padang Besar</option>
                    <option>ETS 15 - Gemas → KL Sentral</option>
                </select>
            </div>

            <div class="field">
                <label>SCHEDULE</label>
                <select id="scheduleSelect">
                    <option>Wed 18 Nov - 07:05</option>
                    <option>Wed 18 Nov - 12:05</option>
                    <option>Wed 18 Nov - 18:05</option>
                </select>
            </div>

            <div class="field">
                <label>SEGMENT FROM</label>
                <select id="fromSelect">
                    <option>KL Sentral</option>
                    <option>Ipoh</option>
                    <option>Taiping</option>
                    <option>Butterworth</option>
                </select>
            </div>

            <div class="field">
                <label>SEGMENT TO</label>
                <select id="toSelect">
                    <option>Butterworth</option>
                    <option>Ipoh</option>
                    <option>Taiping</option>
                    <option>KL Sentral</option>
                </select>
            </div>

            <div class="field">
                <label>CLASS</label>
                <select id="classSelect">
                    <option>All</option>
                    <option>Gold</option>
                    <option>Silver</option>
                </select>
            </div>

            <div class="field">
                <label>COACH</label>
                <select id="coachSelect">
                    <option>All</option>
                    <option>Coach C</option>
                    <option>Coach D</option>
                    <option>Coach E</option>
                    <option>Coach F</option>
                    <option>Coach G</option>
                </select>
            </div>

            <div class="field">
                <label>SEAT STATUS</label>
                <select id="statusSelect">
                    <option>All</option>
                    <option>Available</option>
                    <option>Occupied</option>
                    <option>Reserved</option>
                    <option>Unavailable</option>
                </select>
            </div>

            <div class="field">
                <label>SEAT NUMBER</label>
                <input type="text" id="seatSearch" placeholder="e.g. 4B">
            </div>

            <div class="route-note">
                Viewing: ETS 23 ETS Gold · Wed, 18 Nov · 07:05 ·
                KL Sentral → Butterworth
            </div>

            <button class="outline reset" onclick="resetFilters()">
                Reset to full route
            </button>
        </section>

        //summary cards
        <section class="summary-grid">

            <div class="stat panel">
                <span>Total seats</span>
                <strong id="total">240</strong>
                <small>8 coaches in view</small>
            </div>

            <div class="stat panel">
                <span>Available seats</span>
                <strong id="available">119</strong>
                <small>For this segment</small>
            </div>

            <div class="stat panel">
                <span>Occupied seats</span>
                <strong id="occupied">72</strong>
                <small>30% occupancy</small>
            </div>

            <div class="stat panel">
                <span>Reserved seats</span>
                <strong id="reserved">23</strong>
                <small>Special hold</small>
            </div>

            <div class="stat panel">
                <span>Unavailable / maintenance</span>
                <strong id="unavailable">26</strong>
                <small>Out of service</small>
            </div>

        </section>

        //seat information
        <div class="content-grid">

            <section class="map-panel panel">

                <div class="panel-heading">
                    <h2>Coach seat map</h2>
                    <span class="hint">Select a seat to view details</span>
                </div>

                //buttons
                <div class="coach-tabs" id="coachTabs">
                    <button class="tab active" data-coach="C">Coach C · Gold</button>
                    <button class="tab" data-coach="D">Coach D · Gold</button>
                    <button class="tab" data-coach="E">Coach E · Gold</button>
                    <button class="tab" data-coach="F">Coach F · Silver</button>
                    <button class="tab" data-coach="G">Coach G · Silver</button>
                </div>

                //legend
                <div class="legend">
                    <span><i class="available"></i> Available</span>
                    <span><i class="occupied"></i> Occupied</span>
                    <span><i class="reserved"></i> Reserved</span>
                    <span><i class="unavailable"></i> Unavailable</span>
                    <span><i class="selected"></i> Selected</span>
                </div>

                //seat map
                <div class="seat-map-wrap">
                    <div id="seatMap" class="seat-map"></div>
                </div>

            </section>

            //seat information
            <aside class="detail-panel panel">

                <h2>Seat information</h2>

                <p class="hint">
                    Select a seat on the map to see details and available actions.
                </p>

                <div id="seatInfo" class="seat-info empty">
                    No seat selected
                </div>

                <div id="seatActions" class="seat-actions"></div>

            </aside>

        </div>

        //table
        <section class="coaches-panel panel">

            <div class="panel-heading">
                <h2>Coaches on ETS 23</h2>
                <button class="small-primary" onclick="addCoach()">+ Add coach</button>
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>COACH</th>
                            <th>CLASS</th>
                            <th>ROWS</th>
                            <th>SEATS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody id="coachTable"></tbody>
                </table>
            </div>

        </section>

    </main>
</div>

<script src="admin.js"></script>
</body>
</html>