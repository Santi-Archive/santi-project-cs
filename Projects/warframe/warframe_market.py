import requests
import tkinter as tk

def get_price():
    ...

# UI Setup
root = tk.Tk()
root.title("Warframe Price Checker")
root.geometry("800x600")
root.resizable(False, False)

tk.Label(root, text="Enter Warframe Name:").grid(row=0, column=1, columnspan=2, pady=15)
warframe_entry = tk.Entry(root, width=25, font=("Arial", 16))
warframe_entry.grid(row=1, column=1, columnspan=2)

check_button = tk.Button(root, text="Check Price", command=get_price)
check_button.grid(row=2, column=1, columnspan=2, pady=20)

result_box1 = tk.Frame(root, width=180, height=400, bg="lightgray", relief="solid", bd=1)
result_box1.grid(row=3, column=0, padx=10, pady=10)

result_box2 = tk.Frame(root, width=180, height=400, bg="lightgray", relief="solid", bd=1)
result_box2.grid(row=3, column=1, padx=10, pady=10)

result_box3 = tk.Frame(root, width=180, height=400, bg="lightgray", relief="solid", bd=1)
result_box3.grid(row=3, column=2, padx=10, pady=10)

result_box4 = tk.Frame(root, width=180, height=400, bg="lightgray", relief="solid", bd=1)
result_box4.grid(row=3, column=3, padx=10, pady=10)

root.mainloop()