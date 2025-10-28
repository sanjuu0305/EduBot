import streamlit as st
from openai import OpenAI

st.set_page_config(page_title="EduBot 🤖", page_icon="📚", layout="centered")

st.title("💬 EducationBot")
st.caption("Your personal AI study buddy — ask me anything!")

client = OpenAI(api_key="your_api_key_here")

# Chat state
if "messages" not in st.session_state:
    st.session_state["messages"] = [{"role": "system", "content": "You are EduBot, a friendly AI tutor for students."}]

for msg in st.session_state.messages:
    if msg["role"] != "system":
        with st.chat_message(msg["role"]):
            st.markdown(msg["content"])

# User input
if prompt := st.chat_input("Ask me something..."):
    st.session_state.messages.append({"role": "user", "content": prompt})
    with st.chat_message("user"):
        st.markdown(prompt)

    # Generate AI reply
    with st.chat_message("assistant"):
        stream = client.chat.completions.create(
            model="gpt-5",
            messages=st.session_state.messages,
            stream=True,
        )
        response = st.write_stream(stream)
    st.session_state.messages.append({"role": "assistant", "content": response})
