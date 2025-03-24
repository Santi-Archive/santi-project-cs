import json
import tkinter as tk
import random

with open("./GKQ/quiz_data.json", "r") as file:
    data = json.load(file)
num_questions = len(data)

score = 0
question_index = []
scoreboard = None
life_points = 3

def check_answer(index, choices, buttons):
    global score, scoreboard, life_points

    correct_answer = data[index]["answer"]
    user_choice = data[index]["choices"][choices]

    for i in range(4):
        if data[index]["choices"][i] == correct_answer:
            buttons[i].config(fg="green", bg="black")
        elif i == choices:
            buttons[i].config(fg="red", bg="black")

    if user_choice == correct_answer:
        score += 1
        scoreboard.config(text=f"Score: {score}")
    else:
        life_points -= 1
    
    if life_points == 0:
        game_over()
        return

    root.after(2000, start_game)

def generate_question(question_label):
    global question_index
    index = generate_index(question_index)

    question_label.config(text=data[index]["question"])
    choices = 0
    buttons = []

    for i in range(4):
        choice_button = tk.Button(root, text=data[index]["choices"][choices], width=10, command=lambda c=i: check_answer(index, c, buttons))  
        choice_button.grid(row=2, column=i, padx=5, pady=5)
        choices += 1
        buttons.append(choice_button)

def generate_index(question_index):
    global num_questions
    if len(question_index) == len(data):
        game_over()
        return None
    
    while True:
        i = random.randint(0, num_questions-1)
        if i not in question_index:
            question_index.append(i)
            return i

def start_game():
    global scoreboard
    for widget in root.winfo_children():
        widget.destroy()
    
    scoreboard = tk.Label(root, font=("Arial", 12), text=f"Score: {score}")
    scoreboard.grid(row=0, column=0, columnspan=4, pady=5)
    question_label = tk.Label(root, font=("Arial", 16))
    question_label.grid(row=1, column=0, columnspan=4, pady=10)

    generate_question(question_label)

def game_over():
    global score, question_index, life_points
    for widget in root.winfo_children():
        widget.destroy()

    game_over_label = tk.Label(root, text=f"Game Over!\nFinal Score: {score}", font=("Arial", 20))
    game_over_label.pack(pady=20)

    score = 0
    question_index = []
    life_points = 3

    restart_button = tk.Button(root, text="Restart", font=("Arial", 16), command=start_game)
    restart_button.pack()

def title_screen_show():
    for widget in root.winfo_children():
        widget.destroy()

    title_screen_label = tk.Label(root, text="Welcome to GKQ!", font=("Impact", 24))
    title_screen_label.pack(pady=25)

    title_screen_button = tk.Button(root, text="START", font=("Impact", 16), command=start_game)
    title_screen_button.pack()

# UI
root = tk.Tk()
root.title("GKQ")
root.geometry("550x150")
root.resizable(False, False)

title_screen_show()
root.mainloop()