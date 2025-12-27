package com.clinic.screen.ui.inquiry.adapters

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.RecyclerView
import com.clinic.screen.R
import com.clinic.screen.data.model.Schedule

class ScheduleItemAdapter(
    private val schedules: List<Schedule>
) : RecyclerView.Adapter<ScheduleItemAdapter.ScheduleViewHolder>() {

    inner class ScheduleViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val timeTextView: TextView = itemView.findViewById(R.id.timeText)
        val clinicTextView: TextView = itemView.findViewById(R.id.clinicText)
        val floorTextView: TextView = itemView.findViewById(R.id.floorText)
        val scheduleCard: CardView = itemView.findViewById(R.id.scheduleCard)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ScheduleViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_schedule, parent, false)
        return ScheduleViewHolder(view)
    }

    override fun onBindViewHolder(holder: ScheduleViewHolder, position: Int) {
        val schedule = schedules[position]
        
        // Format time (HH:mm - HH:mm)
        val startTime = if (schedule.startTime.length >= 5) {
            schedule.startTime.substring(0, 5)
        } else {
            schedule.startTime
        }
        val endTime = if (schedule.endTime.length >= 5) {
            schedule.endTime.substring(0, 5)
        } else {
            schedule.endTime
        }
        
        holder.timeTextView.text = "$startTime - $endTime"
        holder.clinicTextView.text = schedule.clinicNumber
        holder.floorTextView.text = "ط. ${schedule.floor}"
    }

    override fun getItemCount(): Int = schedules.size
}

