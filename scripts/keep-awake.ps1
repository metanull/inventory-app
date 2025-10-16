# Keep-Awake Script
# Moves the mouse left and right every 5 minutes to prevent screen lock/sleep
# Press Ctrl+C to stop the script

Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

# Import user32.dll for mouse events
Add-Type @"
using System;
using System.Runtime.InteropServices;
public class MouseHelper {
    [DllImport("user32.dll", CharSet = CharSet.Auto, CallingConvention = CallingConvention.StdCall)]
    public static extern void mouse_event(uint dwFlags, uint dx, uint dy, uint cButtons, uint dwExtraInfo);
    
    public const uint MOUSEEVENTF_MIDDLEDOWN = 0x0020;
    public const uint MOUSEEVENTF_MIDDLEUP = 0x0040;
}
"@

Write-Host "Keep-Awake script started. Press Ctrl+C to stop." -ForegroundColor Green

$interval = 15 # 5 minutes in seconds
$moveDuration = 3 # Duration of mouse movement in seconds
$moveDistance = 50 # Pixels to move

while ($true) {
    # Wait for the interval
    Start-Sleep -Seconds $interval
    
    # Print a dot for this iteration (no newline)
    Write-Host "." -NoNewline

    [System.Windows.Forms.SendKeys]::SendWait("{ESC}")  # ESC
    Start-Sleep -Milliseconds 200
    
    # Get current mouse position
    $currentPos = [System.Windows.Forms.Cursor]::Position
    
    # Send key presses at the beginning (Ctrl+A, wait, then ESC)
    [System.Windows.Forms.SendKeys]::SendWait("^a")  # Ctrl+A
    Start-Sleep -Milliseconds 50
    [System.Windows.Forms.SendKeys]::SendWait("{ESC}")  # ESC
    Start-Sleep -Milliseconds 200
    
    # Move mouse left and right for a few seconds
    $startTime = Get-Date
    while (((Get-Date) - $startTime).TotalSeconds -lt $moveDuration) {
        # Move right
        [System.Windows.Forms.Cursor]::Position = New-Object System.Drawing.Point(($currentPos.X + $moveDistance), $currentPos.Y)
        Start-Sleep -Milliseconds 500
        
        # Move left
        [System.Windows.Forms.Cursor]::Position = New-Object System.Drawing.Point(($currentPos.X - $moveDistance), $currentPos.Y)
        Start-Sleep -Milliseconds 500
    }
    
    # Return to original position
    [System.Windows.Forms.Cursor]::Position = $currentPos
    Start-Sleep -Milliseconds 200
    
    # Send mouse clicks at the end (middle-click, then ESC)
    [MouseHelper]::mouse_event([MouseHelper]::MOUSEEVENTF_MIDDLEDOWN, 0, 0, 0, 0)
    Start-Sleep -Milliseconds 50
    [MouseHelper]::mouse_event([MouseHelper]::MOUSEEVENTF_MIDDLEUP, 0, 0, 0, 0)
    Start-Sleep -Milliseconds 100
    [System.Windows.Forms.SendKeys]::SendWait("{ESC}")  # ESC
}
