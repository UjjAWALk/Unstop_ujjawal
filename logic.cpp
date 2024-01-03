

#include <iostream>
#include <vector>

using namespace std;

// Function to check if consecutive seats are available in a row
bool areSeatsAvailable(const vector<int>& row, int numSeats)
{
    int consecutiveCount = 0;
    for (int seat : row) {
        if (seat == 0) { // Check if seat is available
            consecutiveCount++;
            if (consecutiveCount == numSeats)
                return true;
        } else {
            consecutiveCount = 0;
        }
    }
    return false;
}


void reserveSeats(vector<vector<int>>& coach, int numSeats)
{
    int rows = coach.size();
    int lastRowSeats = coach[rows - 1].size();

    
    for (int i = 0; i < rows; i++) {
        if (areSeatsAvailable(coach[i], numSeats)) {
            
            for (int j = 0; j < coach[i].size(); j++) {
                if (coach[i][j] == 0 && numSeats > 0) {
                    coach[i][j] = 1;
                    numSeats--;
                }
            }
            cout << "Seats reserved successfully in one row." << endl;
            return;
        }
    }

  
    for (int i = 0; i < rows; i++) {
        for (int j = 0; j < coach[i].size(); j++) {
            if (coach[i][j] == 0 && numSeats > 0) {
                coach[i][j] = 1;
                numSeats--;
            }
        }
    }

    cout << "Seats reserved nearby." << endl;
}

void displayCoach(const vector<vector<int>>& coach)
{
    for (const vector<int>& row : coach) {
        for (int seat : row) {
            cout << seat << " ";
        }
        cout << endl;
    }
}

int main()
{
    vector<vector<int>> coach(12, vector<int>(7, 0)); 
    coach[11].resize(3); 
    int numBookings;
    cout << "Enter the number of bookings: ";
    cin >> numBookings;

    for (int i = 0; i < numBookings; i++) {
        int numSeats;
        cout << "Enter the number of seats to reserve: ";
        cin >> numSeats;

        reserveSeats(coach, numSeats);

        cout << "Updated seating arrangement:" << endl;
        displayCoach(coach);
        cout << endl;
    }

    return 0;
}
